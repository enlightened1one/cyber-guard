<?php

define('APP_ROOT', dirname(__DIR__));

function loadEnvFile(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"");
        if ($key !== '') {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

loadEnvFile(APP_ROOT . '/.env');

define('APP_ENV', getenv('APP_ENV') ?: 'development');

$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
$requestIsHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    || str_contains($forwardedProto, 'https');
$requestHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$requestOrigin = ($requestIsHttps ? 'https://' : 'http://') . $requestHost;
$configuredUrl = getenv('APP_URL') ?: getenv('RENDER_EXTERNAL_URL');
if ($configuredUrl && str_contains(parse_url($configuredUrl, PHP_URL_HOST) ?? '', 'localhost')) {
    $configuredUrl = null;
}
define('APP_URL', rtrim($configuredUrl ?: $requestOrigin, '/'));
define('BASE_URL', APP_URL);
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Africa/Lagos');
date_default_timezone_set(APP_TIMEZONE);

require_once APP_ROOT . '/app/Core/Database.php';
require_once APP_ROOT . '/app/Core/Session.php';
require_once APP_ROOT . '/app/Core/Auth.php';
require_once APP_ROOT . '/app/Core/View.php';
require_once APP_ROOT . '/app/Core/Validator.php';
require_once APP_ROOT . '/app/Core/Csrf.php';
require_once APP_ROOT . '/app/Models/UserModel.php';
require_once APP_ROOT . '/app/Models/CourseModel.php';
require_once APP_ROOT . '/app/Models/EnrollmentModel.php';
require_once APP_ROOT . '/app/Models/AnnouncementModel.php';
require_once APP_ROOT . '/app/Models/ExerciseModel.php';
require_once APP_ROOT . '/app/Models/AssignmentModel.php';
require_once APP_ROOT . '/app/Models/QuizModel.php';
require_once APP_ROOT . '/app/Controllers/PublicController.php';
require_once APP_ROOT . '/app/Controllers/AuthController.php';
require_once APP_ROOT . '/app/Controllers/DashboardController.php';
require_once APP_ROOT . '/app/Controllers/AdminController.php';
require_once APP_ROOT . '/app/Controllers/AssignmentController.php';
require_once APP_ROOT . '/app/Controllers/QuizController.php';
require_once APP_ROOT . '/app/Controllers/ExerciseController.php';
