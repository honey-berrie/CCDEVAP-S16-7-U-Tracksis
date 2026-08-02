<?php

namespace App\Middleware;


class AuthMiddleware
{
    // require user to be authenticated with a specific role
    public static function requireAuth(?string $role = null): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // if a specific role is required, check it
        if ($role !== null && ($_SESSION['user_role'] ?? '') !== $role) {
            http_response_code(403);
            echo "403 — Forbidden: You do not have access to this page.";
            exit;
        }
    }

    // log the user into the session
    public static function login(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
    }

    // logout
    public static function logout(): void
    {
        session_unset();
        session_destroy();
    }
}