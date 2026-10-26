<?php

class AssignmentController
{
    public static function index(): string
    {
        $user = Auth::requireLogin();
        if ($user['role'] === 'student') {
            $assignments = AssignmentModel::forUser((int) $user['id']);
        } else {
            $assignments = AssignmentModel::forInstructor((int) $user['id'], $user['role']);
        }

        return View::page('assignments', [
            'user' => $user,
            'assignments' => $assignments,
            'role' => $user['role'],
        ]);
    }

    public static function view(int $assignmentId): string
    {
        $user = Auth::requireLogin();
        $assignment = AssignmentModel::find($assignmentId);
        if (!$assignment) {
            http_response_code(404);
            return View::page('404');
        }

        if ($user['role'] === 'student' && !EnrollmentModel::isEnrolled((int) $user['id'], (int) $assignment['course_id'])) {
            http_response_code(403);
            return View::page('denied', ['user' => $user]);
        }
        if ($user['role'] === 'lecturer' && (int) $assignment['instructor_id'] !== (int) $user['id']) {
            http_response_code(403);
            return View::page('denied', ['user' => $user]);
        }

        $submission = AssignmentModel::submissionForUser($assignmentId, (int) $user['id']);
        $submissions = in_array($user['role'], ['lecturer', 'admin'], true) ? AssignmentModel::submissionsForAssignment($assignmentId) : [];

        return View::page('assignment', [
            'user' => $user,
            'assignment' => $assignment,
            'submission' => $submission,
            'submissions' => $submissions,
            'canGrade' => in_array($user['role'], ['lecturer', 'admin'], true),
        ]);
    }

    public static function submit(int $assignmentId): void
    {
        $user = Auth::requireLogin();
        if ($user['role'] !== 'student') {
            http_response_code(403);
            echo View::page('denied', ['user' => $user]);
            exit;
        }

        if (!Csrf::validate()) {
            Session::flash('error', 'Invalid security token.');
            header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
            exit;
        }

        $assignment = AssignmentModel::find($assignmentId);
        if (!$assignment) {
            http_response_code(404);
            echo View::page('404');
            exit;
        }

        if (!EnrollmentModel::isEnrolled((int) $user['id'], (int) $assignment['course_id'])) {
            Session::flash('error', 'You are not enrolled in this course.');
            header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
            exit;
        }

        $answer = trim($_POST['answer'] ?? '');
        $upload = $_FILES['submission_file'] ?? null;
        $hasUpload = is_array($upload) && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        $maxUploadBytes = max(1, (int) (getenv('UPLOAD_MAX_BYTES') ?: 5 * 1024 * 1024));
        $maxUploadMegabytes = (int) ceil($maxUploadBytes / (1024 * 1024));
        if ($answer === '' && !$hasUpload) {
            Session::flash('error', 'Provide a written response or attach a file before submitting.');
            header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
            exit;
        }

        $fileName = null;
        $filePath = null;
        if ($hasUpload) {
            $uploadError = (int) $upload['error'];
            if ($uploadError !== UPLOAD_ERR_OK) {
                Session::flash('error', $uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE
                    ? 'The file is larger than the allowed ' . $maxUploadMegabytes . ' MB.'
                    : 'The file upload could not be completed. Please retry.');
                header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
                exit;
            }
            if ((int) $upload['size'] <= 0 || (int) $upload['size'] > $maxUploadBytes || !is_uploaded_file($upload['tmp_name'])) {
                Session::flash('error', 'Choose a valid file no larger than ' . $maxUploadMegabytes . ' MB.');
                header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
                exit;
            }

            $originalName = basename((string) $upload['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedTypes = [
                'pdf' => ['application/pdf'],
                'doc' => ['application/msword', 'application/CDFV2', 'application/x-ole-storage'],
                'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
                'txt' => ['text/plain'],
            ];
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->file($upload['tmp_name']);
            if (!isset($allowedTypes[$extension]) || !in_array($mimeType, $allowedTypes[$extension], true)) {
                Session::flash('error', 'Only PDF, DOC, DOCX, and TXT assignment files are accepted.');
                header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
                exit;
            }

            $storageDirectory = APP_ROOT . '/storage/submissions';
            if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0700, true) && !is_dir($storageDirectory)) {
                throw new RuntimeException('Unable to create private assignment upload storage.');
            }
            $storedName = bin2hex(random_bytes(24)) . '.' . $extension;
            $filePath = 'storage/submissions/' . $storedName;
            if (!move_uploaded_file($upload['tmp_name'], APP_ROOT . '/' . $filePath)) {
                throw new RuntimeException('Unable to save the uploaded assignment file.');
            }
            $fileName = $originalName;
        }

        AssignmentModel::submit($assignmentId, (int) $user['id'], $answer, $fileName, $filePath);
        Session::flash('success', 'Your assignment has been submitted successfully.');
        header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
        exit;
    }

    public static function grade(int $assignmentId): void
    {
        $user = Auth::requireRole(['lecturer', 'admin']);
        if (!Csrf::validate()) {
            Session::flash('error', 'Invalid security token.');
            header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
            exit;
        }

        $submissionId = (int) ($_POST['submission_id'] ?? 0);
        $grade = (int) ($_POST['grade'] ?? 0);
        $feedback = trim($_POST['feedback'] ?? '');

        if ($submissionId <= 0 || !AssignmentModel::find($assignmentId)) {
            Session::flash('error', 'A valid submission is required.');
            header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
            exit;
        }

        if (!AssignmentModel::gradeSubmission($assignmentId, $submissionId, (int) $user['id'], $user['role'], $grade, $feedback)) {
            http_response_code(403);
            Session::flash('error', 'The submission could not be graded. Check the assignment, course access, and mark range.');
            header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
            exit;
        }
        Session::flash('success', 'The submission has been graded.');
        header('Location: ' . BASE_URL . '/assignment/' . $assignmentId);
        exit;
    }

    public static function download(int $submissionId): void
    {
        $user = Auth::requireLogin();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT s.file_path, s.file_name, s.user_id, c.instructor_id FROM assignment_submissions s JOIN assignments a ON a.id = s.assignment_id JOIN courses c ON c.id = a.course_id WHERE s.id = :id LIMIT 1');
        $stmt->execute([':id' => $submissionId]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$submission) {
            http_response_code(404);
            exit('Submission file not found.');
        }

        $allowed = $user['role'] === 'admin'
            || ((int) $submission['user_id'] === (int) $user['id'] && $user['role'] === 'student')
            || ((int) $submission['instructor_id'] === (int) $user['id'] && $user['role'] === 'lecturer');
        if (!$allowed) {
            http_response_code(403);
            exit('You are not authorized to access this file.');
        }

        $path = APP_ROOT . '/' . (string) $submission['file_path'];
        $storageRoot = realpath(APP_ROOT . '/storage/submissions');
        $realPath = realpath($path);
        if (!$storageRoot || !$realPath || strpos($realPath, $storageRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($realPath)) {
            http_response_code(404);
            exit('Submission file not found.');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($realPath));
        header('Content-Disposition: attachment; filename="' . str_replace(['"', "\r", "\n"], '', basename((string) $submission['file_name'])) . '"');
        readfile($realPath);
    }
}
