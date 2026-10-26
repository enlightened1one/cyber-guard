<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/database/seed.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

Database::runMigrations();
runSeed(Database::getConnection());

$pdo = Database::getConnection();
$user = UserModel::findByEmail('admin@cyberguard.edu.ng');
assertTrue((bool) $user, 'Admin seed user missing.');
assertTrue((password_verify('Admin@123', $user['password_hash'])), 'Admin password mismatch.');

$courseCount = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
assertTrue($courseCount >= 8, 'At least eight seeded courses are required.');

$studentCount = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "student"')->fetchColumn();
assertTrue($studentCount >= 8, 'At least eight seeded students are required.');

$institutionCount = (int) $pdo->query('SELECT COUNT(*) FROM institutions')->fetchColumn();
assertTrue($institutionCount >= 2, 'At least two seeded institutions are required.');

$quizCount = (int) $pdo->query('SELECT COUNT(DISTINCT course_id) FROM quizzes')->fetchColumn();
assertTrue($quizCount >= 8, 'Every seeded course must have a quiz.');

$duplicateQuizCount = (int) $pdo->query("SELECT COUNT(*) FROM (SELECT course_id FROM quizzes WHERE title = 'Knowledge check' GROUP BY course_id HAVING COUNT(*) > 1)")->fetchColumn();
assertTrue($duplicateQuizCount === 0, 'Seeded quiz data must not duplicate quizzes for a course.');

$quizQuestionCount = (int) $pdo->query('SELECT COUNT(*) FROM quiz_questions')->fetchColumn();
assertTrue($quizQuestionCount >= 16, 'Seeded quizzes must contain student questions.');

$submissionColumns = array_column($pdo->query('PRAGMA table_info(assignment_submissions)')->fetchAll(PDO::FETCH_ASSOC), 'name');
assertTrue(in_array('file_path', $submissionColumns, true), 'Assignment file storage migration is missing.');

$exerciseSubmissionColumns = array_column($pdo->query('PRAGMA table_info(exercise_submissions)')->fetchAll(PDO::FETCH_ASSOC), 'name');
assertTrue(in_array('status', $exerciseSubmissionColumns, true), 'Exercise submission status migration is missing.');
assertTrue(in_array('feedback', $exerciseSubmissionColumns, true), 'Exercise submission feedback migration is missing.');

echo "Smoke test passed.\n";
