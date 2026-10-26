<?php

class QuizModel
{
    public static function forCourse(int $courseId, ?int $userId = null): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, course_id, title, description, pass_mark, time_limit_minutes FROM quizzes WHERE course_id = :course_id ORDER BY id ASC');
        $stmt->execute([':course_id' => $courseId]);
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($userId !== null) {
            $attempts = $pdo->prepare('SELECT quiz_id, COUNT(*) AS attempt_count, MAX(submitted_at) AS latest_attempt FROM quiz_attempts WHERE user_id = :user_id GROUP BY quiz_id');
            $attempts->execute([':user_id' => $userId]);
            $byQuiz = [];
            foreach ($attempts->fetchAll(PDO::FETCH_ASSOC) as $attempt) {
                $byQuiz[(int) $attempt['quiz_id']] = $attempt;
            }
            foreach ($quizzes as &$quiz) {
                $quiz['attempt_count'] = (int) ($byQuiz[(int) $quiz['id']]['attempt_count'] ?? 0);
                $quiz['latest_attempt'] = $byQuiz[(int) $quiz['id']]['latest_attempt'] ?? null;
            }
            unset($quiz);
        }

        return $quizzes;
    }

    public static function find(int $quizId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT q.*, c.title AS course_title, c.id AS course_id, c.slug AS course_slug FROM quizzes q JOIN courses c ON c.id = q.course_id WHERE q.id = :id LIMIT 1');
        $stmt->execute([':id' => $quizId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function questions(int $quizId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, question_text, question_type, points FROM quiz_questions WHERE quiz_id = :quiz_id ORDER BY id ASC');
        $stmt->execute([':quiz_id' => $quizId]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $options = $pdo->prepare('SELECT id, option_text FROM quiz_options WHERE question_id = :question_id ORDER BY id ASC');
        foreach ($questions as &$question) {
            $options->execute([':question_id' => $question['id']]);
            $question['options'] = $options->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($question);
        return $questions;
    }

    public static function attemptsForUser(int $quizId, int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT attempt_no, score, submitted_at FROM quiz_attempts WHERE quiz_id = :quiz_id AND user_id = :user_id ORDER BY attempt_no DESC');
        $stmt->execute([':quiz_id' => $quizId, ':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function submit(int $quizId, int $userId, array $answers): ?array
    {
        $pdo = Database::getConnection();
        $questions = self::questions($quizId);
        if (!$questions || count($answers) !== count($questions)) {
            return null;
        }

        $correctPoints = 0;
        $totalPoints = 0;
        $answerRows = [];
        foreach ($questions as $question) {
            $questionId = (int) $question['id'];
            $selectedOptionId = filter_var($answers[$questionId] ?? null, FILTER_VALIDATE_INT);
            if ($selectedOptionId === false || $selectedOptionId === null) {
                return null;
            }

            $pdoOption = $pdo->prepare('SELECT is_correct FROM quiz_options WHERE id = :option_id AND question_id = :question_id LIMIT 1');
            $pdoOption->execute([':option_id' => $selectedOptionId, ':question_id' => $questionId]);
            $option = $pdoOption->fetch(PDO::FETCH_ASSOC);
            if (!$option) {
                return null;
            }

            $isCorrect = (int) $option['is_correct'] === 1;
            $points = (int) $question['points'];
            $totalPoints += $points;
            if ($isCorrect) {
                $correctPoints += $points;
            }
            $answerRows[] = [
                'question_id' => $questionId,
                'option_id' => $selectedOptionId,
                'is_correct' => $isCorrect,
                'points' => $isCorrect ? $points : 0,
            ];
        }

        if ($totalPoints <= 0) {
            return null;
        }
        $score = (int) round(($correctPoints / $totalPoints) * 100);

        $pdo->beginTransaction();
        try {
            $attemptNumber = $pdo->prepare('SELECT COALESCE(MAX(attempt_no), 0) + 1 FROM quiz_attempts WHERE quiz_id = :quiz_id AND user_id = :user_id');
            $attemptNumber->execute([':quiz_id' => $quizId, ':user_id' => $userId]);
            $attemptNo = (int) $attemptNumber->fetchColumn();
            $insertAttempt = $pdo->prepare('INSERT INTO quiz_attempts (quiz_id, user_id, attempt_no, score, submitted_at) VALUES (:quiz_id, :user_id, :attempt_no, :score, :submitted_at)');
            $insertAttempt->execute([
                ':quiz_id' => $quizId,
                ':user_id' => $userId,
                ':attempt_no' => $attemptNo,
                ':score' => $score,
                ':submitted_at' => date('Y-m-d H:i:s'),
            ]);
            $attemptId = (int) $pdo->lastInsertId();
            $insertAnswer = $pdo->prepare('INSERT INTO quiz_answers (attempt_id, question_id, answer_text, is_correct, points_awarded) VALUES (:attempt_id, :question_id, :answer_text, :is_correct, :points_awarded)');
            foreach ($answerRows as $answer) {
                $insertAnswer->execute([
                    ':attempt_id' => $attemptId,
                    ':question_id' => $answer['question_id'],
                    ':answer_text' => (string) $answer['option_id'],
                    ':is_correct' => $answer['is_correct'] ? 1 : 0,
                    ':points_awarded' => $answer['points'],
                ]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        return ['attempt_no' => $attemptNo, 'score' => $score];
    }
}
