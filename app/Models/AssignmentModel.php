<?php

class AssignmentModel
{
    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT a.*, c.title AS course_title, c.instructor_id, u.full_name AS instructor_name FROM assignments a JOIN courses c ON c.id = a.course_id LEFT JOIN users u ON u.id = c.instructor_id WHERE a.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function forUser(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT a.*, c.title AS course_title, ce.status AS enrollment_status, asb.id AS submission_id, asb.status AS submission_status, asb.grade, asb.submitted_at FROM course_enrollments ce JOIN assignments a ON a.course_id = ce.course_id JOIN courses c ON c.id = a.course_id LEFT JOIN assignment_submissions asb ON asb.assignment_id = a.id AND asb.user_id = :user_id WHERE ce.user_id = :user_id ORDER BY a.due_at DESC, a.id DESC');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function forInstructor(int $userId, string $role): array
    {
        $pdo = Database::getConnection();
        $query = 'SELECT a.*, c.title AS course_title FROM assignments a JOIN courses c ON c.id = a.course_id';
        $params = [];
        if ($role === 'lecturer') {
            $query .= ' WHERE c.instructor_id = :user_id';
            $params[':user_id'] = $userId;
        }
        $query .= ' ORDER BY a.due_at DESC, a.id DESC';
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function forCourse(int $courseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM assignments WHERE course_id = :course_id ORDER BY due_at DESC, id DESC');
        $stmt->execute([':course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function submissionForUser(int $assignmentId, int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM assignment_submissions WHERE assignment_id = :assignment_id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':assignment_id' => $assignmentId, ':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function submissionsForAssignment(int $assignmentId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT s.*, u.full_name, u.email FROM assignment_submissions s JOIN users u ON u.id = s.user_id WHERE s.assignment_id = :assignment_id ORDER BY s.submitted_at DESC');
        $stmt->execute([':assignment_id' => $assignmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function submit(int $assignmentId, int $userId, string $answer, ?string $fileName = null, ?string $filePath = null): bool
    {
        $answer = trim($answer);
        if ($answer === '' && $filePath === null) {
            return false;
        }

        $pdo = Database::getConnection();
        $existing = self::submissionForUser($assignmentId, $userId);
        if ($existing) {
            $stmt = $pdo->prepare('UPDATE assignment_submissions SET text_answer = :text_answer, file_name = COALESCE(:file_name, file_name), file_path = COALESCE(:file_path, file_path), status = :status, submitted_at = :submitted_at, grade = NULL, feedback = NULL WHERE id = :id');
            $stmt->execute([
                ':text_answer' => $answer,
                ':file_name' => $fileName,
                ':file_path' => $filePath,
                ':status' => 'submitted',
                ':submitted_at' => date('Y-m-d H:i:s'),
                ':id' => $existing['id'],
            ]);
            return true;
        }

        $stmt = $pdo->prepare('INSERT INTO assignment_submissions (assignment_id, user_id, text_answer, file_name, file_path, status, submitted_at) VALUES (:assignment_id, :user_id, :text_answer, :file_name, :file_path, :status, :submitted_at)');
        $stmt->execute([
            ':assignment_id' => $assignmentId,
            ':user_id' => $userId,
            ':text_answer' => $answer,
            ':file_name' => $fileName,
            ':file_path' => $filePath,
            ':status' => 'submitted',
            ':submitted_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public static function gradeSubmission(int $assignmentId, int $submissionId, int $graderId, string $graderRole, int $grade, string $feedback): bool
    {
        $pdo = Database::getConnection();
        $query = 'SELECT a.max_marks FROM assignment_submissions s JOIN assignments a ON a.id = s.assignment_id JOIN courses c ON c.id = a.course_id WHERE s.id = :submission_id AND a.id = :assignment_id';
        if ($graderRole === 'lecturer') {
            $query .= ' AND c.instructor_id = :grader_id';
        }
        $check = $pdo->prepare($query);
        $check->bindValue(':submission_id', $submissionId, PDO::PARAM_INT);
        $check->bindValue(':assignment_id', $assignmentId, PDO::PARAM_INT);
        if ($graderRole === 'lecturer') {
            $check->bindValue(':grader_id', $graderId, PDO::PARAM_INT);
        }
        $check->execute();
        $assignment = $check->fetch(PDO::FETCH_ASSOC);
        if (!$assignment || $grade < 0 || $grade > (int) $assignment['max_marks']) {
            return false;
        }

        $stmt = $pdo->prepare('UPDATE assignment_submissions SET grade = :grade, feedback = :feedback, status = :status WHERE id = :id AND assignment_id = :assignment_id');
        $stmt->execute([
            ':grade' => $grade,
            ':feedback' => $feedback,
            ':status' => 'graded',
            ':id' => $submissionId,
            ':assignment_id' => $assignmentId,
        ]);
        return $stmt->rowCount() > 0;
    }
}
