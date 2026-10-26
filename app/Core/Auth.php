<?php

class Auth
{
    public static function user(): ?array
    {
        Session::start();
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function requireLogin(): ?array
    {
        $user = self::user();
        if (!$user) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        return $user;
    }

    public static function requireRole(array $roles): ?array
    {
        $user = self::requireLogin();
        if (!in_array($user['role'], $roles, true)) {
            http_response_code(403);
            echo View::page('denied', ['user' => $user]);
            exit;
        }
        return $user;
    }

    public static function login(string $email, string $password): ?array
    {
        $email = trim(strtolower($email));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        if ($user['status'] !== 'active') {
            return null;
        }

        Session::start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return $user;
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
