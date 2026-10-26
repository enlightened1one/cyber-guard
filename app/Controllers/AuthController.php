<?php

class AuthController
{
    public static function loginPost(): void
    {
        if (!Csrf::validate()) {
            http_response_code(403);
            Session::flash('error', 'Invalid security token. Please try again.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $email = Validator::normalizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = Auth::login($email, $password);
        if (!$user) {
            Session::flash('error', 'Invalid email or password.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        Session::flash('success', 'Welcome back!');
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    public static function registerPost(): void
    {
        if (!Csrf::validate()) {
            http_response_code(403);
            Session::flash('error', 'Invalid security token.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = Validator::normalizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!Validator::required($fullName, 2) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            Session::flash('error', 'Please provide a valid full name, email, and password of at least 8 characters.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        if (UserModel::findByEmail($email)) {
            Session::flash('error', 'An account with this email already exists.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO users (institution_id, full_name, email, password_hash, role, status, created_at) VALUES (:institution_id, :full_name, :email, :password_hash, :role, :status, :created_at)');
        $stmt->execute([
            ':institution_id' => 1,
            ':full_name' => $fullName,
            ':email' => $email,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => 'student',
            ':status' => 'active',
            ':created_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success', 'Your account has been created. Please sign in.');
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public static function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'You have been signed out.');
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    public static function enrollCourse(): void
    {
        if (!Csrf::validate()) {
            http_response_code(403);
            exit;
        }
        $user = Auth::requireLogin();
        $courseId = (int) ($_POST['course_id'] ?? 0);
        if ($courseId <= 0) {
            header('Location: ' . BASE_URL . '/catalog');
            exit;
        }
        $course = CourseModel::find($courseId);
        EnrollmentModel::enroll((int) $user['id'], $courseId);
        Session::flash('success', 'Course enrolled successfully.');
        $slug = $course['slug'] ?? 'catalog';
        header('Location: ' . BASE_URL . '/course/' . rawurlencode((string) $slug));
        exit;
    }
}
