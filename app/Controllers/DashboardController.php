<?php

class DashboardController
{
    public static function dashboard(): string
    {
        $user = Auth::requireLogin();
        $role = $user['role'];

        if ($role === 'student') {
            return self::studentDashboard($user);
        }
        if ($role === 'lecturer') {
            return self::lecturerDashboard($user);
        }
        if ($role === 'admin') {
            return self::adminDashboard($user);
        }

        return View::page('404');
    }

    private static function studentDashboard(array $user): string
    {
        $courses = EnrollmentModel::forUser((int) $user['id']);
        $assignments = AssignmentModel::forUser((int) $user['id']);
        $announcements = AnnouncementModel::recent();
        return View::page('dashboard', [
            'user' => $user,
            'section' => 'student',
            'courses' => $courses,
            'assignments' => $assignments,
            'announcements' => $announcements,
        ]);
    }

    private static function lecturerDashboard(array $user): string
    {
        $assignments = AssignmentModel::forInstructor((int) $user['id'], 'lecturer');
        return View::page('dashboard', [
            'user' => $user,
            'section' => 'lecturer',
            'courses' => CourseModel::allPublished(),
            'assignments' => $assignments,
        ]);
    }

    private static function adminDashboard(array $user): string
    {
        $users = UserModel::all();
        $assignments = AssignmentModel::forInstructor((int) $user['id'], 'admin');
        return View::page('dashboard', [
            'user' => $user,
            'section' => 'admin',
            'users' => $users,
            'courses' => CourseModel::allPublished(),
            'assignments' => $assignments,
        ]);
    }
}
