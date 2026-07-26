<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\User;

// handles login and logout
class AuthController extends Controller
{
    // show the login form
    public function showLoginForm(): void
    {
        // if already logged in
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/test/dashboard');
            return;
        }
        $this->renderPlain('auth/auth');
    }

    // process login credentials
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->renderPlain('auth/auth', ['error' => 'Email and password are required.']);
            return;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->renderPlain('auth/auth', ['error' => 'Invalid email or password.']);
            return;
        }

        // log the user in via middleware
        AuthMiddleware::login($user);

        // redirect based on role
        switch ($user['role']) {
            case 'admin':
                $this->redirect('/test/dashboard');
                break;
            case 'student':
                $this->redirect('/test/dashboard');
                break;
            case 'adviser':
                $this->redirect('/test/dashboard');
                break;
            default:
                $this->redirect('/');
                break;
        }
    }

    // destroy session and redirect
    public function logout(): void
    {
        AuthMiddleware::logout();
        $this->redirect('/login');
    }
}