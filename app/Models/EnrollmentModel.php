<?php

class EnrollmentModel
{
    public static function isEnrolled(int $userId, int $courseId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT 1 FROM course_enrollments WHERE user_id = :user_id AND course_id = :course_id LIMIT 1');
        $stmt->execute([':user_id' => $userId, ':course_id' => $courseId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function enroll(int $userId, int $courseId): bool
    {
        if (self::isEnrolled($userId, $courseId)) {
            return true;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO course_enrollments (user_id, course_id, status) VALUES (:user_id, :course_id, :status)');
        $stmt->execute([':user_id' => $userId, ':course_id' => $courseId, ':status' => 'active']);
        return true;
    }

    public static function forUser(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT ce.*, c.title, c.slug, c.difficulty, c.status FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE ce.user_id = :user_id ORDER BY ce.enrolled_at DESC');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
