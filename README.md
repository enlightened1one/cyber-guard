# Cybersecurity LMS for Nigerian Tertiary Institutions

This project is a working educational prototype of a cybersecurity learning management system designed for Nigerian tertiary institutions.

## Stack
- PHP 8.3+
- SQLite for the local demo database (supports MySQL-compatible PDO wiring when configured)
- Bootstrap 5 and vanilla JavaScript
- PHP native session and CSRF protection

## Local quick start
1. Open a terminal in this project directory.
2. Ensure PHP 8.3 is installed and available on PATH.
3. Copy `.env.example` to `.env` if needed.
4. Run: `php -c .\php.ini bin/migrate.php`
5. Run: `php -c .\php.ini bin/seed.php`
6. Start the PHP built-in server: `php -c .\php.ini -S localhost:8000 -t public`
7. Open http://localhost:8000

## Demo credentials
- Admin: `admin@cyberguard.edu.ng` / `Admin@123`
- Lecturer: `ada.ogbuehi@cyberguard.edu.ng` / `Lecturer@123`
- Student: `tobi.adebayo@cyberguard.edu.ng` / `Student@123`

## Security notes
- The application uses PDO prepared statements, CSRF tokens, password hashing, and restrictive authorization checks.
- Sensitive uploads are stored outside the public web root and sanitized before use.
- This prototype uses a local SQLite database for immediate demo operation.

## Migration and seeding
- `php bin/migrate.php` creates schema objects.
- `php bin/seed.php` inserts deterministic demo data without duplicating records.

## Included learning features
- Role-based homepage, catalogue, and dashboards for student, lecturer, and admin users.
- A practical cybersecurity exercise library with browser-safe scenarios and course-linked labs.
- Students enrolled in a course can take its multiple-choice quizzes, see automatically calculated scores, and review prior attempts.
- Enrolled students can complete practical exercises by submitting written analysis; lecturers can review attempts, award marks, and leave feedback.
- Assignment submissions support written responses and private PDF, DOC, DOCX, or TXT attachments up to 5 MB; students and the course lecturer can access submitted files according to their role.
- A local `/health` endpoint that returns non-sensitive status information for operational checks.

## Testing
- `php tests/smoke.php`

## Composer note
The current environment could not complete a remote Composer installation because outbound TLS validation failed in this sandbox. This demo therefore uses a local PHP-only project setup with a `composer.json` manifest for compatibility, while the application code remains runnable with native PHP commands.
