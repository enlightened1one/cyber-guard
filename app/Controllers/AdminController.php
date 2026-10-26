<?php

class AdminController
{
    public static function index(): string
    {
        Auth::requireRole(['admin']);
        $users = UserModel::all();
        $courses = CourseModel::all();
        $lecturers = UserModel::lecturers();

        return View::page('admin', [
            'users' => $users,
            'courses' => $courses,
            'lecturers' => $lecturers,
            'stats' => [
                'users' => count($users),
                'courses' => count($courses),
                'lecturers' => count($lecturers),
            ],
        ]);
    }

    public static function saveCourse(): void
    {
        Auth::requireRole(['admin']);
        if (!Csrf::validate()) {
            http_response_code(403);
            Session::flash('error', 'Invalid security token.');
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        $courseId = (int) ($_POST['course_id'] ?? 0);
        $payload = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'learning_objectives' => trim((string) ($_POST['learning_objectives'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? 'General')),
            'difficulty' => trim((string) ($_POST['difficulty'] ?? 'Beginner')),
            'duration_weeks' => (int) ($_POST['duration_weeks'] ?? 6),
            'status' => trim((string) ($_POST['status'] ?? 'published')),
            'institution_id' => (int) ($_POST['institution_id'] ?? 1),
            'instructor_id' => (int) ($_POST['instructor_id'] ?? 0),
        ];

        if ($payload['title'] === '') {
            Session::flash('error', 'Course title is required.');
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        try {
            if ($courseId > 0) {
                CourseModel::update($courseId, $payload);
                Session::flash('success', 'Course updated successfully.');
            } else {
                CourseModel::create($payload);
                Session::flash('success', 'Course created successfully.');
            }
        } catch (Throwable $e) {
            Session::flash('error', 'Unable to save the course: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        header('Location: ' . BASE_URL . '/admin');
        exit;
    }

    public static function deleteCourse(): void
    {
        Auth::requireRole(['admin']);
        if (!Csrf::validate()) {
            http_response_code(403);
            Session::flash('error', 'Invalid security token.');
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        $courseId = (int) ($_POST['course_id'] ?? 0);
        if ($courseId <= 0) {
            Session::flash('error', 'A valid course ID is required.');
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        CourseModel::delete($courseId);
        Session::flash('success', 'Course removed successfully.');
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }
}
