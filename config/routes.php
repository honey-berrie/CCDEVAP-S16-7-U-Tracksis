<?php

// Route Definitions - centralized URL mapping

use App\Controllers\AuthController;
use App\Controllers\Test\DashboardController;
use App\Controllers\Student\StudentDashboardController;
use App\Core\Router;

$router = new Router();

// public routes (no auth required)
// Root → redirect to login (or dashboard if already logged in)
$router->get('/', [AuthController::class, 'showLoginForm']);

$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// private routes
$router->group('auth:student', function (Router $router) {
    $router->get('/student/dashboard', [StudentDashboardController::class, 'index']);
});

// Use the stripped URI set by index.php (handles subdirectory installations)
$uri = $_SERVER['APP_REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($uri, $method);