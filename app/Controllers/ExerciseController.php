<?php

class ExerciseController
{
    public static function view(int $exerciseId): string
    {
        $exercise = ExerciseModel::find($exerciseId);
        if (!$exercise) {
            http_response_code(404);
            return View::page('404');
        }

        $user = Auth::user();
        $isEnrolled = $user && $user['role'] === 'student'
            ? EnrollmentModel::isEnrolled((int) $user['id'], (int) $exercise['course_id'])
            : false;
        $canGrade = $user && ($user['role'] === 'admin'
            || ($user['role'] === 'lecturer' && (int) $exercise['instructor_id'] === (int) $user['id']));

        if ($user && $user['role'] === 'student' && !$isEnrolled) {
            http_response_code(403);
            return View::page('denied', ['user' => $user]);
        }

        return View::page('exercise', [
            'exercise' => $exercise,
            'user' => $user,
            'isEnrolled' => $isEnrolled,
            'canGrade' => $canGrade,
            'submissions' => $user && $user['role'] === 'student'
                ? ExerciseModel::submissionsForUser($exerciseId, (int) $user['id'])
                : [],
            'reviewSubmissions' => $canGrade ? ExerciseModel::submissionsForExercise($exerciseId) : [],
        ]);
    }

    public static function submit(int $exerciseId): void
    {
        $user = Auth::requireRole(['student']);
        if (!Csrf::validate()) {
            Session::flash('error', 'Invalid security token. Please try again.');
            header('Location: ' . BASE_URL . '/exercise/' . $exerciseId);
            exit;
        }

        $exercise = ExerciseModel::find($exerciseId);
        if (!$exercise) {
            http_response_code(404);
            echo View::page('404');
            exit;
        }
        if (!EnrollmentModel::isEnrolled((int) $user['id'], (int) $exercise['course_id'])) {
            http_response_code(403);
            echo View::page('denied', ['user' => $user]);
            exit;
        }

        $answer = trim((string) ($_POST['answer'] ?? ''));
        if (strlen($answer) < 30 || strlen($answer) > 20000) {
            Session::flash('error', 'Write a response of at least 30 characters and no more than 20,000 characters.');
            header('Location: ' . BASE_URL . '/exercise/' . $exerciseId);
            exit;
        }

        ExerciseModel::submit($exerciseId, (int) $user['id'], $answer);
        Session::flash('success', 'Your exercise response has been submitted for review.');
        header('Location: ' . BASE_URL . '/exercise/' . $exerciseId);
        exit;
    }

    public static function grade(int $exerciseId): void
    {
        $user = Auth::requireRole(['lecturer', 'admin']);
        if (!Csrf::validate()) {
            Session::flash('error', 'Invalid security token. Please try again.');
            header('Location: ' . BASE_URL . '/exercise/' . $exerciseId);
            exit;
        }

        $exercise = ExerciseModel::find($exerciseId);
        $submissionId = (int) ($_POST['submission_id'] ?? 0);
        $score = filter_var($_POST['score'] ?? null, FILTER_VALIDATE_INT);
        $feedback = trim((string) ($_POST['feedback'] ?? ''));

        if (!$exercise || $submissionId <= 0 || $score === false || $score === null) {
            Session::flash('error', 'Provide a valid exercise submission and score.');
            header('Location: ' . BASE_URL . '/exercise/' . $exerciseId);
            exit;
        }
        if ($user['role'] === 'lecturer' && (int) $exercise['instructor_id'] !== (int) $user['id']) {
            http_response_code(403);
            echo View::page('denied', ['user' => $user]);
            exit;
        }

        if (!ExerciseModel::grade($exerciseId, $submissionId, (int) $user['id'], $user['role'], $score, $feedback)) {
            Session::flash('error', 'The score must be between 0 and ' . (int) $exercise['max_score'] . ', and the submission must belong to this exercise.');
            header('Location: ' . BASE_URL . '/exercise/' . $exerciseId);
            exit;
        }

        Session::flash('success', 'Exercise submission graded successfully.');
        header('Location: ' . BASE_URL . '/exercise/' . $exerciseId);
        exit;
    }
}
