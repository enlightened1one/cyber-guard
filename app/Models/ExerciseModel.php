<?php

class ExerciseModel
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query(
            'SELECT pe.*, c.title AS course_title FROM practical_exercises pe JOIN courses c ON c.id = pe.course_id ORDER BY pe.id ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function featured(): array
    {
        return array_slice(self::all(), 0, 6);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT pe.*, c.title AS course_title, c.instructor_id, c.slug AS course_slug FROM practical_exercises pe JOIN courses c ON c.id = pe.course_id WHERE pe.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function submissionsForUser(int $exerciseId, int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM exercise_submissions WHERE exercise_id = :exercise_id AND user_id = :user_id ORDER BY submitted_at DESC, id DESC');
        $stmt->execute([':exercise_id' => $exerciseId, ':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function submissionsForExercise(int $exerciseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT s.*, u.full_name, u.email FROM exercise_submissions s JOIN users u ON u.id = s.user_id WHERE s.exercise_id = :exercise_id ORDER BY s.submitted_at DESC, s.id DESC');
        $stmt->execute([':exercise_id' => $exerciseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function submit(int $exerciseId, int $userId, string $answer): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO exercise_submissions (exercise_id, user_id, answer_text, score, status, submitted_at) VALUES (:exercise_id, :user_id, :answer_text, 0, :status, :submitted_at)');
        $stmt->execute([
            ':exercise_id' => $exerciseId,
            ':user_id' => $userId,
            ':answer_text' => trim($answer),
            ':status' => 'submitted',
            ':submitted_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function grade(int $exerciseId, int $submissionId, int $graderId, string $graderRole, int $score, string $feedback): bool
    {
        $pdo = Database::getConnection();
        $query = 'SELECT pe.max_score FROM exercise_submissions s JOIN practical_exercises pe ON pe.id = s.exercise_id JOIN courses c ON c.id = pe.course_id WHERE s.id = :submission_id AND pe.id = :exercise_id';
        if ($graderRole === 'lecturer') {
            $query .= ' AND c.instructor_id = :grader_id';
        }
        $check = $pdo->prepare($query);
        $check->bindValue(':submission_id', $submissionId, PDO::PARAM_INT);
        $check->bindValue(':exercise_id', $exerciseId, PDO::PARAM_INT);
        if ($graderRole === 'lecturer') {
            $check->bindValue(':grader_id', $graderId, PDO::PARAM_INT);
        }
        $check->execute();
        $exercise = $check->fetch(PDO::FETCH_ASSOC);
        if (!$exercise || $score < 0 || $score > (int) $exercise['max_score']) {
            return false;
        }

        $update = $pdo->prepare('UPDATE exercise_submissions SET score = :score, feedback = :feedback, status = :status, graded_at = :graded_at WHERE id = :id AND exercise_id = :exercise_id');
        $update->execute([
            ':score' => $score,
            ':feedback' => $feedback,
            ':status' => 'graded',
            ':graded_at' => date('Y-m-d H:i:s'),
            ':id' => $submissionId,
            ':exercise_id' => $exerciseId,
        ]);
        return $update->rowCount() > 0;
    }
}
