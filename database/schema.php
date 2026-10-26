<?php

function migrateDatabase(PDO $pdo): void
{
    $statements = [
        "CREATE TABLE IF NOT EXISTS institutions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            short_name VARCHAR(100),
            country VARCHAR(120) DEFAULT 'Nigeria',
            type VARCHAR(80) DEFAULT 'University',
            is_demo INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(name)
        )",
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            institution_id INTEGER,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(40) NOT NULL DEFAULT 'student',
            status VARCHAR(40) NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL,
            UNIQUE(email)
        )",
        "CREATE TABLE IF NOT EXISTS courses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            institution_id INTEGER,
            instructor_id INTEGER,
            slug VARCHAR(255) NOT NULL UNIQUE,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            learning_objectives TEXT,
            category VARCHAR(100),
            difficulty VARCHAR(50) DEFAULT 'Beginner',
            duration_weeks INTEGER DEFAULT 6,
            status VARCHAR(40) DEFAULT 'published',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL,
            FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS course_enrollments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            course_id INTEGER NOT NULL,
            status VARCHAR(40) DEFAULT 'active',
            enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, course_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS modules (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            course_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            sort_order INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS lessons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            module_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            summary TEXT,
            content TEXT,
            duration_minutes INTEGER DEFAULT 20,
            sort_order INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS lesson_completions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            lesson_id INTEGER NOT NULL,
            completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, lesson_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS assignments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            course_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            due_at DATETIME,
            max_marks INTEGER DEFAULT 100,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS assignment_submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            assignment_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            text_answer TEXT,
            file_name VARCHAR(255),
            status VARCHAR(40) DEFAULT 'submitted',
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            grade INTEGER,
            feedback TEXT,
            FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS quizzes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            course_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            pass_mark INTEGER DEFAULT 50,
            time_limit_minutes INTEGER DEFAULT 30,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS quiz_questions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            quiz_id INTEGER NOT NULL,
            question_text TEXT NOT NULL,
            question_type VARCHAR(40) DEFAULT 'multiple_choice',
            points INTEGER DEFAULT 1,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS quiz_options (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            question_id INTEGER NOT NULL,
            option_text VARCHAR(255) NOT NULL,
            is_correct INTEGER DEFAULT 0,
            FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS quiz_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            quiz_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            attempt_no INTEGER DEFAULT 1,
            score INTEGER DEFAULT 0,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS quiz_answers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            attempt_id INTEGER NOT NULL,
            question_id INTEGER NOT NULL,
            answer_text TEXT,
            is_correct INTEGER DEFAULT 0,
            points_awarded INTEGER DEFAULT 0,
            FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS practical_exercises (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            course_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            challenge_type VARCHAR(100),
            difficulty VARCHAR(50),
            instructions TEXT,
            max_score INTEGER DEFAULT 100,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS exercise_submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            exercise_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            answer_text TEXT,
            score INTEGER DEFAULT 0,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (exercise_id) REFERENCES practical_exercises(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS announcements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            institution_id INTEGER,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            message TEXT NOT NULL,
            is_read INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS security_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            event_type VARCHAR(80),
            details TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS settings (
            key_name VARCHAR(255) PRIMARY KEY,
            value_text TEXT
        )"
    ];

    foreach ($statements as $sql) {
        $pdo->exec($sql);
    }

    $submissionColumns = array_column($pdo->query('PRAGMA table_info(assignment_submissions)')->fetchAll(PDO::FETCH_ASSOC), 'name');
    if (!in_array('file_path', $submissionColumns, true)) {
        $pdo->exec('ALTER TABLE assignment_submissions ADD COLUMN file_path VARCHAR(255)');
    }

    $exerciseSubmissionColumns = array_column($pdo->query('PRAGMA table_info(exercise_submissions)')->fetchAll(PDO::FETCH_ASSOC), 'name');
    if (!in_array('status', $exerciseSubmissionColumns, true)) {
        $pdo->exec("ALTER TABLE exercise_submissions ADD COLUMN status VARCHAR(40) NOT NULL DEFAULT 'submitted'");
    }
    if (!in_array('feedback', $exerciseSubmissionColumns, true)) {
        $pdo->exec('ALTER TABLE exercise_submissions ADD COLUMN feedback TEXT');
    }
    if (!in_array('graded_at', $exerciseSubmissionColumns, true)) {
        $pdo->exec('ALTER TABLE exercise_submissions ADD COLUMN graded_at DATETIME');
    }

    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_quiz_attempts_quiz_user_attempt ON quiz_attempts (quiz_id, user_id, attempt_no)');
    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_quiz_answers_attempt_question ON quiz_answers (attempt_id, question_id)');
}
