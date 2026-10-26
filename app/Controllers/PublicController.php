<?php

class PublicController
{
    public static function home(): string
    {
        $courses = CourseModel::allPublished();
        $announcements = AnnouncementModel::recent();
        return View::page('home', [
            'courses' => array_slice($courses, 0, 6),
            'announcements' => $announcements,
        ]);
    }

    public static function about(): string
    {
        return View::page('about');
    }

    public static function catalog(): string
    {
        $courses = CourseModel::allPublished();
        return View::page('catalog', ['courses' => $courses]);
    }

    public static function course(string $slug): string
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM courses WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$course) {
            http_response_code(404);
            return View::page('404');
        }

        $modules = CourseModel::modules((int) $course['id']);
        foreach ($modules as &$module) {
            $module['lessons'] = CourseModel::lessons((int) $module['id']);
        }
        $user = Auth::user();
        $isEnrolled = $user ? EnrollmentModel::isEnrolled((int) $user['id'], (int) $course['id']) : false;
        $quizzes = QuizModel::forCourse((int) $course['id'], $isEnrolled ? (int) $user['id'] : null);

        return View::page('course', [
            'course' => $course,
            'modules' => $modules,
            'isEnrolled' => $isEnrolled,
            'quizzes' => $quizzes,
            'user' => $user,
        ]);
    }

    public static function signIn(): string
    {
        return View::page('login');
    }

    public static function register(): string
    {
        return View::page('register');
    }

    public static function forgotPassword(): string
    {
        return View::page('forgot-password');
    }

    public static function resetPassword(): string
    {
        return View::page('reset-password');
    }

    public static function privacy(): string
    {
        return View::page('privacy');
    }

    public static function terms(): string
    {
        return View::page('terms');
    }

    public static function contact(): string
    {
        return View::page('contact');
    }

    public static function exercises(): string
    {
        $exercises = ExerciseModel::featured();
        return View::page('exercises', ['exercises' => $exercises]);
    }

    public static function exercise(int $id): string
    {
        return ExerciseController::view($id);
    }

    public static function health(): string
    {
        $payload = [
            'status' => 'ok',
            'app' => 'CyberGuard LMS',
            'environment' => APP_ENV,
            'timezone' => APP_TIMEZONE,
            'database' => getenv('DB_DRIVER') ?: 'sqlite',
            'timestamp' => date(DATE_ATOM),
        ];
        header('Content-Type: application/json');
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function notFound(): string
    {
        http_response_code(404);
        return View::page('404');
    }
}
