<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\User;

// handles login and logout
class AuthController extends Controller
{

    private function roleRedirect(string $role): void
    {
        switch ($role) {
            case 'admin':
                $this->redirect('/admin/adm-dashboard');
                break;
            case 'student':
                $this->redirect('/student/dashboard');
                break;
            case 'adviser':
                $this->redirect('/adviser/dashboard');
                break;
            default:
                $this->redirect('/');
                break;
        }
    }

    // show the login form
    public function showLoginForm(): void
    {
        // if already logged in
        if (!empty($_SESSION['user_id'])) {
            $role = $_SESSION['user_role'];
            
            $this->roleRedirect($role);
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
            $this->renderPlain('auth/auth', ['error' => 'Email and password are required.', 'error_type' => 'login']);
            return;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->renderPlain('auth/auth', ['error' => 'Invalid email or password.', 'error_type' => 'login']);
            return;
        }

        if ($user['role'] !== 'admin' && User::isMaintenanceModeOn()) {
            $this->renderPlain('auth/auth', [
                'error' => 'U-Tracksis is currently under maintenance. Please try again later.',
                'error_type' => 'login',
            ]);
            return;
        }

        // log the user in via middleware
        AuthMiddleware::login($user);

        $this->roleRedirect($user['role']);
    }

    public function register(): void
    {
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm'] ?? '';
        $account_type = $_POST['accountType'] ?? '';

        if ($firstname === '' || $lastname === '' || $email === '' || $password === '' || $confirm_password === '' || $account_type === ''){
            $this->renderPlain('auth/auth', ['error' => 'Please fill out all the fields.', 'error_type' => 'register']);
            return;
        }

        if ($account_type !== 'student' && $account_type !== 'faculty') {
            $this->renderPlain('auth/auth', ['error' => 'Invalid account type.', 'error_type' => 'register']); return;
        }

        if (strlen($firstname) > 25 || strlen($lastname) > 25) {
            $this->renderPlain('auth/auth', ['error' => 'Firstname or lastname must not exceed 25 characters.', 'error_type' => 'register']); return;
        }elseif (strlen($firstname) < 3 || strlen($lastname) < 3) {
            $this->renderPlain('auth/auth', ['error' => 'Firstname or lastname must be at least 3 characters.', 'error_type' => 'register']); return;
        } elseif (!preg_match('/^[A-Za-z ]+$/', $firstname) || !preg_match('/^[A-Za-z ]+$/', $lastname)){
            $this->renderPlain('auth/auth', ['error' => 'Firstname or lastname must only contain letters and spaces.', 'error_type' => 'register']); return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $this->renderPlain('auth/auth', ['error' => 'Please enter a valid email address.', 'error_type' => 'register']); return;
        } elseif (strlen($email) > 255){
            $this->renderPlain('auth/auth', ['error' => 'Email is too long.', 'error_type' => 'register']); return;
        }

        if (strlen($password) < 8) {
            $this->renderPlain('auth/auth', ['error' => 'Password must be at least 8 characters.', 'error_type' => 'register']); return;
        }elseif (!preg_match('/[A-Z]/', $password)) {
            $this->renderPlain('auth/auth', ['error' => 'Password must contain at least 1 uppercase letter.', 'error_type' => 'register']); return;
        } elseif (!preg_match('/[0-9]/', $password)) {
            $this->renderPlain('auth/auth', ['error' => 'Password must contain at least 1 number.', 'error_type' => 'register']); return;
        } elseif ($password !== $confirm_password) {
            $this->renderPlain('auth/auth', ['error' => 'Passwords do not match.', 'error_type' => 'register']); return;
        }

        $existingUser = User::findByEmail($email);

        if ($existingUser) {
            $this->renderPlain('auth/auth', ['error' => "Email '$email' is already used.", 'error_type' => 'register']); return;
        }

        $register_result = User::register($email, $firstname, $lastname, $password, $account_type);

        $this->renderPlain('auth/auth', ['success' => $register_result, 'redirect' => 'login']);
    } 

    // destroy session and redirect
    public function logout(): void
    {
        AuthMiddleware::logout();
        $this->redirect('/login');
    }

    public function timeout(): void
    {
        AuthMiddleware::logout();
        $this->redirect('/login?timeout=1');
    }

    public function maintenanceStatus(): void
    {
        $maintenance = User::isMaintenanceModeOn();
        $role = $_SESSION['user_role'] ?? null;
        $exempt = $role === 'admin';

        $this->json([
            'maintenance' => $maintenance,
            'exempt' => $exempt,
            'role' => $role,
        ]);
    }

    public function maintenancePage(): void
    {
        $this->renderPlain('auth/maintenance');
    }
}