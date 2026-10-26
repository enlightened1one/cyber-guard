<?php

function ensureInstitution(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare('SELECT id FROM institutions WHERE name = :name LIMIT 1');
    $stmt->execute([':name' => $data['name']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $update = $pdo->prepare('UPDATE institutions SET short_name = :short_name, type = :type, is_demo = :is_demo WHERE id = :id');
        $update->execute([':short_name' => $data['short_name'], ':type' => $data['type'], ':is_demo' => $data['is_demo'], ':id' => $row['id']]);
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO institutions (name, short_name, country, type, is_demo, created_at) VALUES (:name, :short_name, :country, :type, :is_demo, :created_at)');
    $insert->execute([':name' => $data['name'], ':short_name' => $data['short_name'], ':country' => $data['country'], ':type' => $data['type'], ':is_demo' => $data['is_demo'], ':created_at' => date('Y-m-d H:i:s')]);
    return (int) $pdo->lastInsertId();
}

function ensureUser(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => strtolower($data['email'])]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $update = $pdo->prepare('UPDATE users SET institution_id = :institution_id, full_name = :full_name, password_hash = :password_hash, role = :role, status = :status WHERE id = :id');
        $update->execute([':institution_id' => $data['institution_id'], ':full_name' => $data['full_name'], ':password_hash' => $data['password_hash'], ':role' => $data['role'], ':status' => $data['status'], ':id' => $row['id']]);
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO users (institution_id, full_name, email, password_hash, role, status, created_at) VALUES (:institution_id, :full_name, :email, :password_hash, :role, :status, :created_at)');
    $insert->execute([':institution_id' => $data['institution_id'], ':full_name' => $data['full_name'], ':email' => strtolower($data['email']), ':password_hash' => $data['password_hash'], ':role' => $data['role'], ':status' => $data['status'], ':created_at' => date('Y-m-d H:i:s')]);
    return (int) $pdo->lastInsertId();
}

function ensureCourse(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare('SELECT id FROM courses WHERE slug = :slug LIMIT 1');
    $stmt->execute([':slug' => $data['slug']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $update = $pdo->prepare('UPDATE courses SET institution_id = :institution_id, instructor_id = :instructor_id, title = :title, description = :description, learning_objectives = :learning_objectives, category = :category, difficulty = :difficulty, duration_weeks = :duration_weeks, status = :status WHERE id = :id');
        $update->execute([':institution_id' => $data['institution_id'], ':instructor_id' => $data['instructor_id'], ':title' => $data['title'], ':description' => $data['description'], ':learning_objectives' => $data['learning_objectives'], ':category' => $data['category'], ':difficulty' => $data['difficulty'], ':duration_weeks' => $data['duration_weeks'], ':status' => $data['status'], ':id' => $row['id']]);
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO courses (institution_id, instructor_id, slug, title, description, learning_objectives, category, difficulty, duration_weeks, status, created_at) VALUES (:institution_id, :instructor_id, :slug, :title, :description, :learning_objectives, :category, :difficulty, :duration_weeks, :status, :created_at)');
    $insert->execute([':institution_id' => $data['institution_id'], ':instructor_id' => $data['instructor_id'], ':slug' => $data['slug'], ':title' => $data['title'], ':description' => $data['description'], ':learning_objectives' => $data['learning_objectives'], ':category' => $data['category'], ':difficulty' => $data['difficulty'], ':duration_weeks' => $data['duration_weeks'], ':status' => $data['status'], ':created_at' => date('Y-m-d H:i:s')]);
    return (int) $pdo->lastInsertId();
}

function ensureModule(PDO $pdo, int $courseId, string $title, int $order): int
{
    $stmt = $pdo->prepare('SELECT id FROM modules WHERE course_id = :course_id AND title = :title LIMIT 1');
    $stmt->execute([':course_id' => $courseId, ':title' => $title]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $pdo->prepare('UPDATE modules SET sort_order = :sort_order WHERE id = :id')->execute([':sort_order' => $order, ':id' => $row['id']]);
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO modules (course_id, title, sort_order, created_at) VALUES (:course_id, :title, :sort_order, :created_at)');
    $insert->execute([':course_id' => $courseId, ':title' => $title, ':sort_order' => $order, ':created_at' => date('Y-m-d H:i:s')]);
    return (int) $pdo->lastInsertId();
}

function ensureLesson(PDO $pdo, int $moduleId, string $title, string $summary, int $minutes): int
{
    $stmt = $pdo->prepare('SELECT id FROM lessons WHERE module_id = :module_id AND title = :title LIMIT 1');
    $stmt->execute([':module_id' => $moduleId, ':title' => $title]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $update = $pdo->prepare('UPDATE lessons SET summary = :summary, duration_minutes = :duration_minutes WHERE id = :id');
        $update->execute([':summary' => $summary, ':duration_minutes' => $minutes, ':id' => $row['id']]);
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO lessons (module_id, title, summary, content, duration_minutes, sort_order, created_at) VALUES (:module_id, :title, :summary, :content, :duration_minutes, :sort_order, :created_at)');
    $insert->execute([':module_id' => $moduleId, ':title' => $title, ':summary' => $summary, ':content' => $summary, ':duration_minutes' => $minutes, ':sort_order' => 1, ':created_at' => date('Y-m-d H:i:s')]);
    return (int) $pdo->lastInsertId();
}

function ensureAnnouncement(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare('SELECT id FROM announcements WHERE title = :title LIMIT 1');
    $stmt->execute([':title' => $data['title']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO announcements (institution_id, title, content, created_by, created_at) VALUES (:institution_id, :title, :content, :created_by, :created_at)');
    $insert->execute([':institution_id' => $data['institution_id'], ':title' => $data['title'], ':content' => $data['content'], ':created_by' => $data['created_by'], ':created_at' => date('Y-m-d H:i:s')]);
    return (int) $pdo->lastInsertId();
}

function ensureNotification(PDO $pdo, int $userId, string $message): int
{
    $stmt = $pdo->prepare('SELECT id FROM notifications WHERE user_id = :user_id AND message = :message LIMIT 1');
    $stmt->execute([':user_id' => $userId, ':message' => $message]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (:user_id, :message, 0, :created_at)');
    $insert->execute([':user_id' => $userId, ':message' => $message, ':created_at' => date('Y-m-d H:i:s')]);
    return (int) $pdo->lastInsertId();
}

function ensureExercise(PDO $pdo, int $courseId, string $title, string $challengeType, string $difficulty, string $instructions, int $maxScore): int
{
    $stmt = $pdo->prepare('SELECT id FROM practical_exercises WHERE course_id = :course_id AND title = :title LIMIT 1');
    $stmt->execute([':course_id' => $courseId, ':title' => $title]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $update = $pdo->prepare('UPDATE practical_exercises SET challenge_type = :challenge_type, difficulty = :difficulty, instructions = :instructions, max_score = :max_score WHERE id = :id');
        $update->execute([':challenge_type' => $challengeType, ':difficulty' => $difficulty, ':instructions' => $instructions, ':max_score' => $maxScore, ':id' => $row['id']]);
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO practical_exercises (course_id, title, challenge_type, difficulty, instructions, max_score) VALUES (:course_id, :title, :challenge_type, :difficulty, :instructions, :max_score)');
    $insert->execute([':course_id' => $courseId, ':title' => $title, ':challenge_type' => $challengeType, ':difficulty' => $difficulty, ':instructions' => $instructions, ':max_score' => $maxScore]);
    return (int) $pdo->lastInsertId();
}

function ensureAssignment(PDO $pdo, int $courseId, string $title, string $description, string $dueAt, int $maxMarks): int
{
    $stmt = $pdo->prepare('SELECT id FROM assignments WHERE course_id = :course_id AND title = :title LIMIT 1');
    $stmt->execute([':course_id' => $courseId, ':title' => $title]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $update = $pdo->prepare('UPDATE assignments SET description = :description, due_at = :due_at, max_marks = :max_marks WHERE id = :id');
        $update->execute([':description' => $description, ':due_at' => $dueAt, ':max_marks' => $maxMarks, ':id' => $row['id']]);
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO assignments (course_id, title, description, due_at, max_marks, created_at) VALUES (:course_id, :title, :description, :due_at, :max_marks, :created_at)');
    $insert->execute([':course_id' => $courseId, ':title' => $title, ':description' => $description, ':due_at' => $dueAt, ':max_marks' => $maxMarks, ':created_at' => date('Y-m-d H:i:s')]);
    return (int) $pdo->lastInsertId();
}

function runSeed(PDO $pdo): void
{
    $institutions = [
        ['name' => 'Federal Cybersecurity College', 'short_name' => 'FCC', 'country' => 'Nigeria', 'type' => 'College', 'is_demo' => 1],
        ['name' => 'North-West Institute of Digital Security', 'short_name' => 'NWIDS', 'country' => 'Nigeria', 'type' => 'Institute', 'is_demo' => 1],
    ];

    $institutionIds = [];
    foreach ($institutions as $institution) {
        $institutionIds[] = ensureInstitution($pdo, $institution);
    }

    $adminPassword = password_hash('Admin@123', PASSWORD_DEFAULT);
    $lecturerPassword = password_hash('Lecturer@123', PASSWORD_DEFAULT);
    $studentPassword = password_hash('Student@123', PASSWORD_DEFAULT);

    $adminId = ensureUser($pdo, ['institution_id' => $institutionIds[0], 'full_name' => 'Aisha Bello', 'email' => 'admin@cyberguard.edu.ng', 'password_hash' => $adminPassword, 'role' => 'admin', 'status' => 'active']);
    $lecturerId = ensureUser($pdo, ['institution_id' => $institutionIds[0], 'full_name' => 'Ada Ogbuehi', 'email' => 'ada.ogbuehi@cyberguard.edu.ng', 'password_hash' => $lecturerPassword, 'role' => 'lecturer', 'status' => 'active']);
    $lecturer2Id = ensureUser($pdo, ['institution_id' => $institutionIds[1], 'full_name' => 'Samuel Eze', 'email' => 'samuel.eze@nwids.edu.ng', 'password_hash' => $lecturerPassword, 'role' => 'lecturer', 'status' => 'active']);

    $studentEmails = [
        'tobi.adebayo@cyberguard.edu.ng',
        'chiamaka.okafor@cyberguard.edu.ng',
        'musa.yusuf@cyberguard.edu.ng',
        'ngozi.ikpe@cyberguard.edu.ng',
        'kene.madu@nwids.edu.ng',
        'david.awoniyi@nwids.edu.ng',
        'ifeoma.ajayi@nwids.edu.ng',
        'mariam.garba@nwids.edu.ng',
    ];
    $studentNames = [
        'Tobi Adebayo', 'Chiamaka Okafor', 'Musa Yusuf', 'Ngozi Ikpe', 'Kene Madu', 'David Awoniyi', 'Ifeoma Ajayi', 'Mariam Garba'
    ];
    $studentIds = [];
    foreach ($studentEmails as $index => $email) {
        $studentIds[] = ensureUser($pdo, ['institution_id' => $institutionIds[$index % 2], 'full_name' => $studentNames[$index], 'email' => $email, 'password_hash' => $studentPassword, 'role' => 'student', 'status' => 'active']);
    }

    $courseBlueprints = [
        ['slug' => 'cybersecurity-fundamentals', 'title' => 'Cybersecurity Fundamentals', 'category' => 'Fundamentals', 'difficulty' => 'Beginner', 'description' => 'This introductory course teaches essential principles of confidentiality, integrity, and availability, as well as common attacker tactics and defensive controls.', 'learning_objectives' => 'Understand cyber threats, secure habits, and core defense principles.', 'institution_id' => $institutionIds[0], 'instructor_id' => $lecturerId, 'duration_weeks' => 5, 'status' => 'published'],
        ['slug' => 'network-security-basics', 'title' => 'Networking and Network Security', 'category' => 'Network Security', 'difficulty' => 'Intermediate', 'description' => 'Students learn network topologies, firewalls, segmentation, and common attack vectors such as spoofing and lateral movement.', 'learning_objectives' => 'Secure network design and packet-level threat awareness.', 'institution_id' => $institutionIds[0], 'instructor_id' => $lecturerId, 'duration_weeks' => 7, 'status' => 'published'],
        ['slug' => 'web-app-security', 'title' => 'Web Application Security', 'category' => 'Application Security', 'difficulty' => 'Intermediate', 'description' => 'Focus on the OWASP Top 10 and secure development patterns for web applications used in higher education and business systems.', 'learning_objectives' => 'Recognize and mitigate common web vulnerabilities.', 'institution_id' => $institutionIds[1], 'instructor_id' => $lecturer2Id, 'duration_weeks' => 6, 'status' => 'published'],
        ['slug' => 'secure-coding-principles', 'title' => 'Secure Coding Principles', 'category' => 'Secure Development', 'difficulty' => 'Intermediate', 'description' => 'Learn safe input handling, output encoding, validation, and secure software lifecycle practices for Nigerian institutions and start-ups.', 'learning_objectives' => 'Build resilient software with secure defaults.', 'institution_id' => $institutionIds[1], 'instructor_id' => $lecturer2Id, 'duration_weeks' => 6, 'status' => 'published'],
        ['slug' => 'ethical-hacking-awareness', 'title' => 'Ethical Hacking and Authorization', 'category' => 'Offensive Security', 'difficulty' => 'Advanced', 'description' => 'Students understand responsible disclosure, authorization boundaries, and legal, ethical, and technical safeguards in testing workflows.', 'learning_objectives' => 'Apply ethical testing within an authorized scope.', 'institution_id' => $institutionIds[0], 'instructor_id' => $lecturerId, 'duration_weeks' => 8, 'status' => 'published'],
        ['slug' => 'incident-response', 'title' => 'Incident Response Fundamentals', 'category' => 'Operations', 'difficulty' => 'Intermediate', 'description' => 'This course teaches alert triage, containment, evidence handling, and reporting in higher-education technology environments.', 'learning_objectives' => 'Respond effectively to cyber incidents and document evidence.', 'institution_id' => $institutionIds[1], 'instructor_id' => $lecturer2Id, 'duration_weeks' => 5, 'status' => 'published'],
        ['slug' => 'cryptography-fundamentals', 'title' => 'Cryptography Fundamentals', 'category' => 'Cryptography', 'difficulty' => 'Intermediate', 'description' => 'Learn symmetric and asymmetric encryption, digital signatures, key management, and secure communication basics.', 'learning_objectives' => 'Apply cryptographic methods to real security design decisions.', 'institution_id' => $institutionIds[0], 'instructor_id' => $lecturerId, 'duration_weeks' => 5, 'status' => 'published'],
        ['slug' => 'privacy-and-data-protection', 'title' => 'Privacy and Data Protection', 'category' => 'Privacy', 'difficulty' => 'Beginner', 'description' => 'Explore privacy regulations, data minimization, user consent, and the role of records handling in academic and research settings.', 'learning_objectives' => 'Protect personal and institutional data responsibly.', 'institution_id' => $institutionIds[1], 'instructor_id' => $lecturer2Id, 'duration_weeks' => 4, 'status' => 'published'],
    ];

    $courseIds = [];
    foreach ($courseBlueprints as $blueprint) {
        $courseIds[] = ensureCourse($pdo, $blueprint);
    }

    $moduleInserts = [
        ['courseIndex' => 0, 'modules' => ['Cybersecurity Concepts', 'Threat Landscape', 'Defensive Controls']],
        ['courseIndex' => 1, 'modules' => ['Host and Network Fundamentals', 'Firewall and Segmentation', 'Monitoring and Hardening']],
        ['courseIndex' => 2, 'modules' => ['HTTP and Session Security', 'OWASP Top 10', 'Secure Deployment Patterns']],
        ['courseIndex' => 3, 'modules' => ['Input Validation', 'Secure Design', 'Code Review Essentials']],
        ['courseIndex' => 4, 'modules' => ['Reconnaissance and Scoping', 'Testing Workflow', 'Responsible Reporting']],
        ['courseIndex' => 5, 'modules' => ['Alert Triage', 'Containment and Recovery', 'Evidence Handling']],
        ['courseIndex' => 6, 'modules' => ['Key Concepts', 'Hashing and Signatures', 'Public Key Infrastructure']],
        ['courseIndex' => 7, 'modules' => ['Privacy Principles', 'Data Handling', 'User Consent and Governance']],
    ];

    foreach ($moduleInserts as $group) {
        $courseId = $courseIds[$group['courseIndex']];
        foreach ($group['modules'] as $index => $title) {
            ensureModule($pdo, $courseId, $title, $index + 1);
        }
    }

    $moduleRows = $pdo->query('SELECT id, course_id, title FROM modules ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($moduleRows as $module) {
        $course = $pdo->prepare('SELECT title FROM courses WHERE id = :id');
        $course->execute([':id' => $module['course_id']]);
        $courseTitle = $course->fetchColumn();
        $lessonTitlePrefix = $courseTitle ?: 'Lesson';
        ensureLesson($pdo, (int) $module['id'], $lessonTitlePrefix . ' Overview', 'Key foundations and practical learning objectives.', 20);
        ensureLesson($pdo, (int) $module['id'], $lessonTitlePrefix . ' Application', 'Hands-on scenario and case study walkthrough.', 25);
        ensureLesson($pdo, (int) $module['id'], $lessonTitlePrefix . ' Challenge', 'Assessment activity and reflective summary.', 30);
    }

    foreach ($studentIds as $studentId) {
        foreach ($courseIds as $courseId) {
            $pdo->prepare('INSERT OR IGNORE INTO course_enrollments (user_id, course_id, status, enrolled_at) VALUES (:user_id, :course_id, :status, :enrolled_at)')->execute([
                ':user_id' => $studentId,
                ':course_id' => $courseId,
                ':status' => 'active',
                ':enrolled_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    $exerciseBlueprints = [
        ['course_index' => 0, 'title' => 'Phishing message triage', 'challenge_type' => 'email analysis', 'difficulty' => 'Beginner', 'instructions' => 'You receive a message claiming to be from a registrar. It asks for an urgent password reset and contains a link to a fake portal. Which of the following actions is the safest response?'],
        ['course_index' => 0, 'title' => 'Password safety check', 'challenge_type' => 'password review', 'difficulty' => 'Beginner', 'instructions' => 'Review the sample password, identify the security weaknesses, and explain how to improve it using multi-factor authentication and a password manager.'],
        ['course_index' => 1, 'title' => 'Firewall decision scenario', 'challenge_type' => 'network defense', 'difficulty' => 'Intermediate', 'instructions' => 'A host on the student lab network starts sending unusual outbound traffic. Which steps should be taken first to contain the incident and preserve evidence?'],
        ['course_index' => 1, 'title' => 'Segmentation challenge', 'challenge_type' => 'architecture review', 'difficulty' => 'Intermediate', 'instructions' => 'A department has a shared Wi-Fi network connected to multiple systems. Describe which design changes would reduce lateral movement risk without affecting user access.'],
        ['course_index' => 2, 'title' => 'Broken access control review', 'challenge_type' => 'secure coding', 'difficulty' => 'Intermediate', 'instructions' => 'Study a vulnerable request flow and identify where the application fails to verify authorization before revealing private records or personal data.'],
        ['course_index' => 2, 'title' => 'SQL injection awareness', 'challenge_type' => 'input validation', 'difficulty' => 'Intermediate', 'instructions' => 'Compare a vulnerable database query with a parameterized secure alternative and explain how prepared statements prevent attackers from altering query logic.'],
        ['course_index' => 3, 'title' => 'Output encoding lab', 'challenge_type' => 'web defense', 'difficulty' => 'Intermediate', 'instructions' => 'A comment field is rendered unsafely. Identify the risk and explain how escaping user-controlled input prevents cross-site scripting.'],
        ['course_index' => 3, 'title' => 'Secure code review', 'challenge_type' => 'code inspection', 'difficulty' => 'Intermediate', 'instructions' => 'Inspect the insecure pattern where raw user data is concatenated into a string and propose safer validation and sanitization techniques.'],
        ['course_index' => 4, 'title' => 'Authorization boundaries', 'challenge_type' => 'ethical testing', 'difficulty' => 'Advanced', 'instructions' => 'During an authorized assessment, identify which actions should be blocked if they exceed the agreed testing scope or violate user consent.'],
        ['course_index' => 4, 'title' => 'Responsible disclosure review', 'challenge_type' => 'policy analysis', 'difficulty' => 'Advanced', 'instructions' => 'Review the disclosure workflow and define the steps needed before notifying the owner, preserving evidence, and planning remediation.'],
        ['course_index' => 5, 'title' => 'Incident response flow', 'challenge_type' => 'response drill', 'difficulty' => 'Intermediate', 'instructions' => 'A suspicious login spike triggers an alert. Outline the containment, eradication, and recovery steps that should happen first.'],
        ['course_index' => 5, 'title' => 'Log analysis exercise', 'challenge_type' => 'security monitoring', 'difficulty' => 'Intermediate', 'instructions' => 'Review synthetic log entries to identify suspicious patterns such as repeated failed logins, privilege escalation attempts, or unexpected access times.'],
        ['course_index' => 6, 'title' => 'Cryptography match-up', 'challenge_type' => 'key management', 'difficulty' => 'Intermediate', 'instructions' => 'Match each security goal to the appropriate cryptographic primitive, such as hashing, symmetric encryption, or digital signatures.'],
        ['course_index' => 6, 'title' => 'Trust and key lifecycle', 'challenge_type' => 'security design', 'difficulty' => 'Intermediate', 'instructions' => 'Explain why key rotation, secure storage, and expiration policies matter when implementing encrypted campus systems.'],
        ['course_index' => 7, 'title' => 'Privacy by design case', 'challenge_type' => 'data handling', 'difficulty' => 'Beginner', 'instructions' => 'In a student records system, identify where data minimization and user consent should be applied before collecting personal information.'],
        ['course_index' => 7, 'title' => 'Consent and retention review', 'challenge_type' => 'privacy policy', 'difficulty' => 'Beginner', 'instructions' => 'Assess a retention policy and explain which records should be stored only for a valid business purpose and deleted securely thereafter.'],
    ];

    foreach ($exerciseBlueprints as $exercise) {
        $courseId = $courseIds[$exercise['course_index']];
        ensureExercise($pdo, $courseId, $exercise['title'], $exercise['challenge_type'], $exercise['difficulty'], $exercise['instructions'], 100);
    }

    $assignmentBlueprints = [
        ['course_index' => 0, 'title' => 'Phishing response memo', 'description' => 'Draft a short memo explaining how a student or staff member should respond to a suspected phishing email that asks for a password reset and references a fake portal.', 'due_at' => date('Y-m-d H:i:s', strtotime('+7 days')), 'max_marks' => 100],
        ['course_index' => 1, 'title' => 'Network hardening plan', 'description' => 'Create a risk-based hardening checklist for a campus network with Wi-Fi access, student labs, and shared administrative systems.', 'due_at' => date('Y-m-d H:i:s', strtotime('+10 days')), 'max_marks' => 100],
        ['course_index' => 2, 'title' => 'OWASP review brief', 'description' => 'Prepare a brief review of how broken access control and injection flaws could affect a university online portal and propose practical mitigations.', 'due_at' => date('Y-m-d H:i:s', strtotime('+9 days')), 'max_marks' => 100],
        ['course_index' => 3, 'title' => 'Secure coding case study', 'description' => 'Review a vulnerable function and rewrite it using safer validation, encoding, and least-privilege patterns.', 'due_at' => date('Y-m-d H:i:s', strtotime('+12 days')), 'max_marks' => 100],
        ['course_index' => 4, 'title' => 'Ethical testing scope memo', 'description' => 'Document an authorized testing scope, identify prohibited actions, and explain the legal and ethical justification for each boundary.', 'due_at' => date('Y-m-d H:i:s', strtotime('+14 days')), 'max_marks' => 100],
        ['course_index' => 5, 'title' => 'Incident response tabletop', 'description' => 'Describe how your team would respond to a suspected ransomware event affecting academic records and critical administrative systems.', 'due_at' => date('Y-m-d H:i:s', strtotime('+8 days')), 'max_marks' => 100],
        ['course_index' => 6, 'title' => 'Encryption design note', 'description' => 'Explain which encryption approach should be used for stored data, in-transit communication, and digital signatures in a campus system.', 'due_at' => date('Y-m-d H:i:s', strtotime('+11 days')), 'max_marks' => 100],
        ['course_index' => 7, 'title' => 'Privacy and data retention checklist', 'description' => 'Draft a checklist to help the institution collect only necessary student data and securely delete information beyond its retention period.', 'due_at' => date('Y-m-d H:i:s', strtotime('+6 days')), 'max_marks' => 100],
    ];

    foreach ($assignmentBlueprints as $assignmentBlueprint) {
        $courseId = $courseIds[$assignmentBlueprint['course_index']];
        ensureAssignment($pdo, $courseId, $assignmentBlueprint['title'], $assignmentBlueprint['description'], $assignmentBlueprint['due_at'], $assignmentBlueprint['max_marks']);
    }

    $quizQuestions = [
        [
            ['text' => 'A message asks you to reset your campus password using an unfamiliar link. What is the safest response?', 'options' => ['Open the official campus portal directly and report the message', 'Reply with your password to confirm your identity', 'Forward the link to classmates to check if it works', 'Open the link on a personal device instead']],
            ['text' => 'Which practice best protects a student account if a password is exposed?', 'options' => ['Use a unique password and enable multi-factor authentication', 'Reuse the same password with a different capital letter', 'Save the password in a shared document', 'Disable account alerts']],
            ['text' => 'What does the principle of least privilege require?', 'options' => ['Give each account only the access needed for its tasks', 'Give all users administrator access for convenience', 'Share one account across a project team', 'Keep access after a user changes roles']],
            ['text' => 'How should an application safely include user input in a database query?', 'options' => ['Use parameterized queries with bound values', 'Concatenate input directly into SQL text', 'Hide the query in client-side code', 'Remove spaces from the input and trust it']],
            ['text' => 'During an incident response, what should responders do with relevant logs?', 'options' => ['Preserve them and document handling before analysis', 'Delete them to reduce storage use', 'Edit entries to remove confusing events', 'Post them publicly to request help']],
        ],
        [
            ['text' => 'Which control most directly limits the impact of a compromised campus workstation?', 'options' => ['Network segmentation and least-privilege access', 'A single flat network for every system', 'Shared administrator credentials', 'Turning off security monitoring']],
            ['text' => 'What is the main purpose of a firewall rule review?', 'options' => ['Remove unnecessary access and confirm rules match policy', 'Allow every service to simplify troubleshooting', 'Replace endpoint protection', 'Make all internal traffic trusted']],
            ['text' => 'Why should network devices be patched regularly?', 'options' => ['To address known vulnerabilities and improve reliability', 'To make passwords unnecessary', 'To disable audit logs', 'To ensure all devices use the same IP address']],
            ['text' => 'Which signal may indicate a password-guessing attempt?', 'options' => ['A sudden increase in failed logins from one source', 'A scheduled backup completing successfully', 'A user reading a public course page', 'A printer entering sleep mode']],
            ['text' => 'What is a safe first action after confirming unauthorized network access?', 'options' => ['Follow the incident plan to contain affected systems and preserve evidence', 'Erase all affected devices immediately', 'Ignore it until the next maintenance window', 'Publish the affected IP addresses']],
        ],
        [
            ['text' => 'Which measure best reduces SQL injection risk in a web application?', 'options' => ['Parameterized queries and server-side validation', 'Building SQL by concatenating submitted values', 'Relying only on browser-side validation', 'Encoding the database password in the page']],
            ['text' => 'How should an application prevent users from accessing another student’s record?', 'options' => ['Check authorization on the server for every requested record', 'Hide the record link in the page', 'Use an unguessable URL as the only control', 'Trust the user ID sent by the browser']],
            ['text' => 'Why should output be encoded before displaying user-provided text?', 'options' => ['To prevent the browser interpreting it as executable markup or script', 'To make the text searchable by attackers', 'To encrypt the database automatically', 'To avoid requiring access control']],
            ['text' => 'Which cookie attribute helps prevent browser scripts from reading a session cookie?', 'options' => ['HttpOnly', 'Public', 'Readable', 'Indexable']],
            ['text' => 'What is the safest way to handle an authentication error?', 'options' => ['Return a generic message without exposing sensitive system details', 'Show the database query and stack trace', 'Reveal whether the email or password was incorrect', 'Return the password hash for debugging']],
        ],
        [
            ['text' => 'What should a secure coding review do with untrusted input?', 'options' => ['Validate it on the server and use context-appropriate output encoding', 'Assume it is safe if it came from a form', 'Remove only punctuation characters', 'Trust it after JavaScript validation']],
            ['text' => 'Which database access pattern is safer?', 'options' => ['Prepared statements with parameter binding', 'String concatenation using request values', 'A query copied from the browser', 'A shared database administrator account']],
            ['text' => 'What does secure-by-default mean?', 'options' => ['New features start with restrictive, safe settings', 'Every feature is enabled for everyone', 'Security controls are optional after launch', 'Logs are disabled until an incident']],
            ['text' => 'Why should errors shown to end users avoid stack traces?', 'options' => ['They can disclose implementation details useful for further attacks', 'They make the application load too quickly', 'They prevent server-side logging', 'They automatically expose encryption keys']],
            ['text' => 'How should secrets such as API keys be handled?', 'options' => ['Store them in protected configuration and rotate them when exposed', 'Commit them to a public source repository', 'Place them in client-side JavaScript', 'Reuse one key across every environment']],
        ],
        [
            ['text' => 'What is required before conducting a penetration test?', 'options' => ['Written authorization and a clearly agreed scope', 'A public IP address', 'An anonymous account', 'A promise to avoid reporting findings']],
            ['text' => 'What should a tester do when a system appears outside the approved scope?', 'options' => ['Stop testing that system and ask the authorizing owner for guidance', 'Continue if it seems related', 'Try default passwords to confirm exposure', 'Publish the system details']],
            ['text' => 'Which action best supports responsible disclosure?', 'options' => ['Notify the owner privately with reproducible, limited evidence', 'Share exploit details broadly before contacting the owner', 'Access additional records to prove impact', 'Delete logs after testing']],
            ['text' => 'Why should testers minimize access to personal data?', 'options' => ['To reduce harm and respect privacy while validating a finding', 'To make reports less accurate', 'To bypass authorization requirements', 'To prevent the owner from fixing the issue']],
            ['text' => 'What should be included in a responsible assessment report?', 'options' => ['Scope, impact, evidence, and practical remediation guidance', 'Unrelated personal information', 'Credentials collected from users', 'Instructions to attack unrelated systems']],
        ],
        [
            ['text' => 'What is the first priority when an incident is confirmed?', 'options' => ['Follow the response plan to contain impact while preserving evidence', 'Delete all affected logs', 'Notify social media before the response team', 'Rebuild systems without recording actions']],
            ['text' => 'Why should responders preserve a forensic copy of relevant evidence?', 'options' => ['To support reliable investigation and maintain an audit trail', 'To make the affected system slower', 'To avoid documenting the incident', 'To replace access controls']],
            ['text' => 'Which log pattern can be a useful investigation lead?', 'options' => ['Repeated failed logins followed by an unusual successful login', 'A normal scheduled patch record', 'A user opening a public page', 'A routine successful backup']],
            ['text' => 'What is the purpose of containment?', 'options' => ['Limit ongoing damage while investigation and recovery continue', 'Erase evidence before analysis', 'Restore every system without checking integrity', 'Disable all incident communications']],
            ['text' => 'When should an incident be documented?', 'options' => ['Throughout the response, recording actions and timestamps', 'Only if the investigation fails', 'After logs have been deleted', 'Only when an external party asks']],
        ],
        [
            ['text' => 'Which cryptographic mechanism is designed to verify data integrity?', 'options' => ['A cryptographic hash checked against a trusted value', 'Base64 encoding', 'A file extension change', 'A compression algorithm']],
            ['text' => 'What is a digital signature commonly used to provide?', 'options' => ['Integrity and evidence of the signer’s identity', 'Confidentiality by itself', 'Faster network routing', 'Password recovery without verification']],
            ['text' => 'Where should private cryptographic keys be kept?', 'options' => ['In protected storage with access limited to authorized services', 'In a public source repository', 'In a shared spreadsheet', 'Inside a URL parameter']],
            ['text' => 'Why rotate a key after suspected exposure?', 'options' => ['To limit the period in which the exposed key can be abused', 'To make encrypted data public', 'To remove the need for backups', 'To disable authentication']],
            ['text' => 'Which approach is appropriate for protecting data in transit?', 'options' => ['Use a correctly configured authenticated TLS connection', 'Send plaintext and rely on a hidden URL', 'Use a checksum instead of encryption', 'Share the private key with all users']],
        ],
        [
            ['text' => 'What does data minimization mean?', 'options' => ['Collect only personal data needed for a defined purpose', 'Collect every available detail in case it is useful', 'Keep all records forever', 'Share student data with every department']],
            ['text' => 'When should an institution delete personal data?', 'options' => ['When it is no longer required under the approved retention purpose', 'Immediately after every login', 'Only when storage is full', 'Never, once collected']],
            ['text' => 'Which practice helps protect student records?', 'options' => ['Limit access to people with a legitimate, authorized need', 'Use one shared account for staff', 'Email records to personal accounts', 'Publish records using predictable URLs']],
            ['text' => 'What should happen before collecting personal information for a new purpose?', 'options' => ['Confirm the purpose, lawful basis, and appropriate notice', 'Collect first and decide later', 'Make all fields mandatory', 'Disable the privacy contact']],
            ['text' => 'How should a suspected privacy breach be handled?', 'options' => ['Report it promptly through the institution’s incident process', 'Delete evidence and avoid reporting', 'Share affected records to ask for advice', 'Wait until the next annual review']],
        ],
    ];

    foreach ($courseIds as $courseIndex => $courseId) {
        $quizLookup = $pdo->prepare('SELECT id FROM quizzes WHERE course_id = :course_id AND title = :title LIMIT 1');
        $quizLookup->execute([':course_id' => $courseId, ':title' => 'Knowledge check']);
        $quizRow = $quizLookup->fetch(PDO::FETCH_ASSOC);
        if ($quizRow) {
            $quizId = (int) $quizRow['id'];
        } else {
            $quiz = $pdo->prepare('INSERT INTO quizzes (course_id, title, description, pass_mark, time_limit_minutes) VALUES (:course_id, :title, :description, :pass_mark, :time_limit_minutes)');
            $quiz->execute([
                ':course_id' => $courseId,
                ':title' => 'Knowledge check',
                ':description' => 'A multiple-choice quiz covering key concepts in this course.',
                ':pass_mark' => 60,
                ':time_limit_minutes' => 20,
            ]);
            $quizId = (int) $pdo->lastInsertId();
        }

        $questionBlueprints = $quizQuestions[$courseIndex] ?? $quizQuestions[0];
        $questionLookup = $pdo->prepare('SELECT id, question_text FROM quiz_questions WHERE quiz_id = :quiz_id ORDER BY id ASC');
        $questionLookup->execute([':quiz_id' => $quizId]);
        $existingQuestions = $questionLookup->fetchAll(PDO::FETCH_ASSOC);
        foreach ($questionBlueprints as $questionIndex => $blueprint) {
            if (isset($existingQuestions[$questionIndex])) {
                $questionId = (int) $existingQuestions[$questionIndex]['id'];
                if (str_starts_with($existingQuestions[$questionIndex]['question_text'], 'Question ')) {
                    $pdo->prepare('UPDATE quiz_questions SET question_text = :question_text WHERE id = :id')
                        ->execute([':question_text' => $blueprint['text'], ':id' => $questionId]);
                }
            } else {
                $questionInsert = $pdo->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, points) VALUES (:quiz_id, :question_text, :question_type, :points)');
                $questionInsert->execute([':quiz_id' => $quizId, ':question_text' => $blueprint['text'], ':question_type' => 'multiple_choice', ':points' => 1]);
                $questionId = (int) $pdo->lastInsertId();
            }

            $optionLookup = $pdo->prepare('SELECT id, option_text FROM quiz_options WHERE question_id = :question_id ORDER BY id ASC');
            $optionLookup->execute([':question_id' => $questionId]);
            $existingOptions = $optionLookup->fetchAll(PDO::FETCH_ASSOC);
            foreach ($blueprint['options'] as $optionIndex => $optionText) {
                $isCorrect = $optionIndex === 0 ? 1 : 0;
                if (isset($existingOptions[$optionIndex])) {
                    if (in_array($existingOptions[$optionIndex]['option_text'], ['Correct practice', 'Common mistake', 'Irrelevant response', 'Unverified action'], true)) {
                        $pdo->prepare('UPDATE quiz_options SET option_text = :option_text, is_correct = :is_correct WHERE id = :id')
                            ->execute([':option_text' => $optionText, ':is_correct' => $isCorrect, ':id' => $existingOptions[$optionIndex]['id']]);
                    }
                } else {
                    $pdo->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (:question_id, :option_text, :is_correct)')
                        ->execute([':question_id' => $questionId, ':option_text' => $optionText, ':is_correct' => $isCorrect]);
                }
            }
        }
    }

    ensureAnnouncement($pdo, ['institution_id' => $institutionIds[0], 'title' => 'Semester cyber awareness week', 'content' => 'Students are invited to complete the phishing simulation and secure coding challenge this week.', 'created_by' => $adminId]);
    ensureAnnouncement($pdo, ['institution_id' => $institutionIds[1], 'title' => 'Faculty workshop on secure development', 'content' => 'Join the workshop on secure coding and privacy-by-design practices for academic systems.', 'created_by' => $lecturer2Id]);

    foreach ($studentIds as $studentId) {
        ensureNotification($pdo, $studentId, 'Your learning dashboard has updated course recommendations.');
    }

    foreach ([$adminId, $lecturerId, $lecturer2Id] as $userId) {
        ensureNotification($pdo, $userId, 'System health is normal and no critical alerts were recorded.');
    }
}
