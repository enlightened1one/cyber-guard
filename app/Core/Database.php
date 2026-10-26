<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $driver = getenv('DB_DRIVER') ?: 'sqlite';
        $dbPath = getenv('DB_PATH') ?: APP_ROOT . '/storage/app.sqlite';
        if (!preg_match('/^[a-zA-Z]:[\\\\\/]|^\\\\|^\//', $dbPath)) {
            $dbPath = APP_ROOT . '/' . ltrim($dbPath, '/\\');
        }

        if ($driver === 'mysql') {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $database = getenv('DB_DATABASE') ?: 'cyber_lms';
            $username = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } else {
            $directory = dirname($dbPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
            $dsn = 'sqlite:' . $dbPath;
            self::$pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
        }

        return self::$pdo;
    }

    public static function runMigrations(): void
    {
        $pdo = self::getConnection();
        require_once APP_ROOT . '/database/schema.php';
        migrateDatabase($pdo);
    }
}
