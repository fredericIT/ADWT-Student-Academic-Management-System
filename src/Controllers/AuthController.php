<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\AuthorizationException;
use App\Services\AuthService;
use App\Services\AuthorizationService;
use App\Services\SessionManager;

/**
 * Controller handling user authentication, session state, and role dashboard routing.
 */
class AuthController
{
    private AuthService $authService;
    private SessionManager $sessionManager;
    private AuthorizationService $authorizationService;

    public function __construct(
        ?AuthService $authService = null,
        ?SessionManager $sessionManager = null,
        ?AuthorizationService $authorizationService = null
    ) {
        $this->authService          = $authService ?? new AuthService();
        $this->sessionManager       = $sessionManager ?? new SessionManager();
        $this->authorizationService = $authorizationService ?? new AuthorizationService($this->sessionManager);
    }

    /**
     * GET /login
     */
    public function showLoginForm(): void
    {
        if ($this->sessionManager->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $error = '';
        $email = '';
        require __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * POST /login
     */
    public function login(): void
    {
        if ($this->sessionManager->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $error    = '';

        if ($email === '' || $password === '') {
            $error = 'Email and password are required.';
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email or password.';
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        try {
            $user = $this->authService->authenticate($email, $password);
            $this->sessionManager->login($user);
            $this->redirect('/dashboard');
        } catch (AuthenticationException $e) {
            $error = $e->getMessage();
            require __DIR__ . '/../../views/auth/login.php';
        }
    }

    /**
     * GET /logout & POST /logout
     */
    public function logout(): void
    {
        $this->sessionManager->logout();
        $this->redirect('/login');
    }

    /**
     * GET /dashboard
     */
    public function dashboard(): void
    {
        try {
            $this->authorizationService->requireAuthentication();
        } catch (AuthorizationException $e) {
            $this->redirect('/login');
        }

        $user = $this->sessionManager->getUser();
        $pageTitle = 'Dashboard';
        require __DIR__ . '/../../views/dashboard/index.php';
    }

    /**
     * GET /student-area
     */
    public function studentArea(): void
    {
        try {
            $this->authorizationService->requireRole('student');
        } catch (AuthorizationException $e) {
            $this->renderUnauthorized($e->getMessage());
            return;
        }

        $user = $this->sessionManager->getUser();
        $pageTitle = 'Student Portal';
        require __DIR__ . '/../../views/dashboard/student.php';
    }

    /**
     * GET /lecturer-area
     */
    public function lecturerArea(): void
    {
        try {
            $this->authorizationService->requireRole('lecturer');
        } catch (AuthorizationException $e) {
            $this->renderUnauthorized($e->getMessage());
            return;
        }

        $user = $this->sessionManager->getUser();
        $pageTitle = 'Lecturer Portal';
        require __DIR__ . '/../../views/dashboard/lecturer.php';
    }

    /**
     * GET /admin-area
     */
    public function adminArea(): void
    {
        try {
            $this->authorizationService->requireRole('administrator');
        } catch (AuthorizationException $e) {
            $this->renderUnauthorized($e->getMessage());
            return;
        }

        $user = $this->sessionManager->getUser();
        $pageTitle = 'Administrator Console';
        require __DIR__ . '/../../views/dashboard/admin.php';
    }

    /**
     * GET /unauthorized
     */
    public function unauthorized(): void
    {
        $this->renderUnauthorized();
    }

    private function renderUnauthorized(?string $message = null): void
    {
        http_response_code(403);
        $pageTitle = '403 — Unauthorized';
        $errorMessage = $message ?? 'You do not have permission to access this resource.';
        require __DIR__ . '/../../views/errors/unauthorized.php';
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
