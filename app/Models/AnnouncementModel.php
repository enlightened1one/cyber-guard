<?php

class AnnouncementModel
{
    public static function recent(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
