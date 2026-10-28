<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';

Session::start();

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = '/' . trim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$routeMethod = $method === 'HEAD' ? 'GET' : $method;

$routes = [
    ['GET', '/', [PublicController::class, 'home']],
    ['GET', '/about', [PublicController::class, 'about']],
    ['GET', '/catalog', [PublicController::class, 'catalog']],
    ['GET', '/contact', [PublicController::class, 'contact']],
    ['GET', '/exercises', [PublicController::class, 'exercises']],
    ['GET', '/health', [PublicController::class, 'health']],
    ['GET', '/privacy', [PublicController::class, 'privacy']],
    ['GET', '/terms', [PublicController::class, 'terms']],
    ['GET', '/login', [PublicController::class, 'signIn']],
    ['POST', '/login', [AuthController::class, 'loginPost']],
    ['GET', '/register', [PublicController::class, 'register']],
    ['POST', '/register', [AuthController::class, 'registerPost']],
    ['GET', '/forgot-password', [PublicController::class, 'forgotPassword']],
    ['GET', '/reset-password', [PublicController::class, 'resetPassword']],
    ['POST', '/logout', [AuthController::class, 'logout']],
    ['POST', '/enroll-course', [AuthController::class, 'enrollCourse']],
    ['GET', '/assignments', [AssignmentController::class, 'index']],
    ['GET', '/dashboard', [DashboardController::class, 'dashboard']],
    ['GET', '/admin', [AdminController::class, 'index']],
    ['POST', '/admin/course/save', [AdminController::class, 'saveCourse']],
    ['POST', '/admin/course/delete', [AdminController::class, 'deleteCourse']],
];

foreach ($routes as [$httpMethod, $path, $handler]) {
    if ($routeMethod === $httpMethod && $uri === $path) {
        echo call_user_func($handler);
        exit;
    }
}

if (preg_match('#^/course/([A-Za-z0-9_-]+)$#', $uri, $matches)) {
    echo PublicController::course($matches[1]);
    exit;
}

if (preg_match('#^/assignment/([0-9]+)/submit$#', $uri, $matches) && $method === 'POST') {
    AssignmentController::submit((int) $matches[1]);
    exit;
}

if (preg_match('#^/assignment/([0-9]+)/grade$#', $uri, $matches) && $method === 'POST') {
    AssignmentController::grade((int) $matches[1]);
    exit;
}

if (preg_match('#^/assignment-submission/([0-9]+)/download$#', $uri, $matches) && $routeMethod === 'GET') {
    AssignmentController::download((int) $matches[1]);
    exit;
}

if (preg_match('#^/assignment/([0-9]+)$#', $uri, $matches)) {
    echo AssignmentController::view((int) $matches[1]);
    exit;
}

if (preg_match('#^/quiz/([0-9]+)$#', $uri, $matches) && $routeMethod === 'GET') {
    echo QuizController::view((int) $matches[1]);
    exit;
}

if (preg_match('#^/quiz/([0-9]+)/submit$#', $uri, $matches) && $method === 'POST') {
    QuizController::submit((int) $matches[1]);
    exit;
}

if (preg_match('#^/exercise/([0-9]+)$#', $uri, $matches)) {
    echo ExerciseController::view((int) $matches[1]);
    exit;
}

if (preg_match('#^/exercise/([0-9]+)/submit$#', $uri, $matches) && $method === 'POST') {
    ExerciseController::submit((int) $matches[1]);
    exit;
}

if (preg_match('#^/exercise/([0-9]+)/grade$#', $uri, $matches) && $method === 'POST') {
    ExerciseController::grade((int) $matches[1]);
    exit;
}

echo PublicController::notFound();
