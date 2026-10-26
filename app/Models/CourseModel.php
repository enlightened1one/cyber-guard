<?php

class CourseModel
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT c.*, i.name AS institution_name, u.full_name AS instructor_name FROM courses c LEFT JOIN institutions i ON i.id = c.institution_id LEFT JOIN users u ON u.id = c.instructor_id ORDER BY c.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allPublished(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT c.*, i.name AS institution_name, u.full_name AS instructor_name FROM courses c LEFT JOIN institutions i ON i.id = c.institution_id LEFT JOIN users u ON u.id = c.instructor_id WHERE c.status = 'published' ORDER BY c.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT c.*, i.name AS institution_name, u.full_name AS instructor_name FROM courses c LEFT JOIN institutions i ON i.id = c.institution_id LEFT JOIN users u ON u.id = c.instructor_id WHERE c.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT c.*, i.name AS institution_name, u.full_name AS instructor_name FROM courses c LEFT JOIN institutions i ON i.id = c.institution_id LEFT JOIN users u ON u.id = c.instructor_id WHERE c.slug = :slug LIMIT 1');
        $stmt->execute([':slug' => trim($slug)]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $title = trim((string) ($data['title'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('Course title is required.');
        }
        if ($slug === '') {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
            $slug = trim((string) $slug, '-');
        }

        $institutionId = (int) ($data['institution_id'] ?? 1);
        $instructorId = (int) ($data['instructor_id'] ?? 0);
        $description = trim((string) ($data['description'] ?? ''));
        $learningObjectives = trim((string) ($data['learning_objectives'] ?? ''));
        $category = trim((string) ($data['category'] ?? 'General'));
        $difficulty = trim((string) ($data['difficulty'] ?? 'Beginner'));
        $durationWeeks = max(1, (int) ($data['duration_weeks'] ?? 6));
        $status = in_array(($data['status'] ?? 'published'), ['draft', 'published'], true) ? ($data['status'] ?? 'published') : 'published';

        $stmt = $pdo->prepare('INSERT INTO courses (institution_id, instructor_id, slug, title, description, learning_objectives, category, difficulty, duration_weeks, status, created_at) VALUES (:institution_id, :instructor_id, :slug, :title, :description, :learning_objectives, :category, :difficulty, :duration_weeks, :status, :created_at)');
        $stmt->execute([
            ':institution_id' => $institutionId,
            ':instructor_id' => $instructorId ?: null,
            ':slug' => $slug,
            ':title' => $title,
            ':description' => $description,
            ':learning_objectives' => $learningObjectives,
            ':category' => $category,
            ':difficulty' => $difficulty,
            ':duration_weeks' => $durationWeeks,
            ':status' => $status,
            ':created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $course = self::find($id);
        if (!$course) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE courses SET institution_id = :institution_id, instructor_id = :instructor_id, slug = :slug, title = :title, description = :description, learning_objectives = :learning_objectives, category = :category, difficulty = :difficulty, duration_weeks = :duration_weeks, status = :status WHERE id = :id');
        $stmt->execute([
            ':institution_id' => (int) ($data['institution_id'] ?? $course['institution_id'] ?? 1),
            ':instructor_id' => isset($data['instructor_id']) ? (int) $data['instructor_id'] : $course['instructor_id'],
            ':slug' => trim((string) ($data['slug'] ?? $course['slug'] ?? '')) ?: $course['slug'],
            ':title' => trim((string) ($data['title'] ?? $course['title'] ?? '')) ?: $course['title'],
            ':description' => trim((string) ($data['description'] ?? $course['description'] ?? '')),
            ':learning_objectives' => trim((string) ($data['learning_objectives'] ?? $course['learning_objectives'] ?? '')),
            ':category' => trim((string) ($data['category'] ?? $course['category'] ?? 'General')),
            ':difficulty' => trim((string) ($data['difficulty'] ?? $course['difficulty'] ?? 'Beginner')),
            ':duration_weeks' => max(1, (int) ($data['duration_weeks'] ?? $course['duration_weeks'] ?? 6)),
            ':status' => in_array(($data['status'] ?? $course['status'] ?? 'published'), ['draft', 'published'], true) ? ($data['status'] ?? $course['status']) : $course['status'],
            ':id' => $id,
        ]);
        return $stmt->rowCount() >= 0;
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM courses WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function modules(int $courseId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM modules WHERE course_id = :course_id ORDER BY sort_order ASC');
        $stmt->execute([':course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function lessons(int $moduleId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM lessons WHERE module_id = :module_id ORDER BY sort_order ASC');
        $stmt->execute([':module_id' => $moduleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
