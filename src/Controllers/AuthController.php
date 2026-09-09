<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;

/**
 * Controller handling user authentication, login and logout.
 */
class AuthController
{
    /**
     * Display the login page.
     */
    public function login(): void
    {
        if (Auth::check() && !isset($_GET['relogin'])) {
            header('Location: /dashboard');
            exit;
        }

        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;
        require __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Process login form submission.
     */
    public function authenticate(): void
    {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Please enter both username/email and password.';
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        if (Auth::attempt($username, $password)) {
            header('Location: /dashboard?success=login');
            exit;
        }

        $error = 'Invalid credentials. Please check your username and password.';
        require __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Log out the current user session.
     */
    public function logout(): void
    {
        Auth::logout();
        header('Location: /login?success=logged_out');
        exit;
    }
}
