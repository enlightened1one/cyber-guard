<?php

class QuizController
{
    public static function view(int $quizId): string
    {
        $user = Auth::requireRole(['student']);
        $quiz = QuizModel::find($quizId);
        if (!$quiz) {
            http_response_code(404);
            return View::page('404');
        }
        if (!EnrollmentModel::isEnrolled((int) $user['id'], (int) $quiz['course_id'])) {
            http_response_code(403);
            return View::page('denied', ['user' => $user]);
        }

        return View::page('quiz', [
            'user' => $user,
            'quiz' => $quiz,
            'questions' => QuizModel::questions($quizId),
            'attempts' => QuizModel::attemptsForUser($quizId, (int) $user['id']),
        ]);
    }

    public static function submit(int $quizId): void
    {
        $user = Auth::requireRole(['student']);
        if (!Csrf::validate()) {
            Session::flash('error', 'Invalid security token. Please try again.');
            header('Location: ' . BASE_URL . '/quiz/' . $quizId);
            exit;
        }

        $quiz = QuizModel::find($quizId);
        if (!$quiz) {
            http_response_code(404);
            echo View::page('404');
            exit;
        }
        if (!EnrollmentModel::isEnrolled((int) $user['id'], (int) $quiz['course_id'])) {
            http_response_code(403);
            echo View::page('denied', ['user' => $user]);
            exit;
        }

        $answers = $_POST['answers'] ?? [];
        if (!is_array($answers)) {
            Session::flash('error', 'Quiz answers were not submitted in a valid format.');
            header('Location: ' . BASE_URL . '/quiz/' . $quizId);
            exit;
        }

        $result = QuizModel::submit($quizId, (int) $user['id'], $answers);
        if ($result === null) {
            Session::flash('error', 'Answer every question using one of the listed options.');
            header('Location: ' . BASE_URL . '/quiz/' . $quizId);
            exit;
        }

        Session::flash('success', 'Quiz submitted. Your score: ' . $result['score'] . '%.');
        header('Location: ' . BASE_URL . '/quiz/' . $quizId);
        exit;
    }
}
