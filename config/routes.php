<?php

// Route Definitions - centralized URL mapping

use App\Controllers\AuthController;
use App\Controllers\Test\DashboardController;
use App\Controllers\Student\StudentDashboardController;
use App\Controllers\Adviser\AdviserDashboardController;
use App\Core\Router;

$router = new Router();

// public routes (no auth required)
$router->get('/', [AuthController::class, 'showLoginForm']);

$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/timeout', [AuthController::class, 'timeout']);

// private routes
$router->group('auth:student', function (Router $router) {
    $router->get('/student/dashboard', [StudentDashboardController::class, 'index']);
    $router->get('/student/group-profile', [StudentDashboardController::class, 'groupProfile']);
    $router->post('/student/group-profile-update', [StudentDashboardController::class, 'updateGroupProfile']);
    $router->get('/student/milestones', [StudentDashboardController::class, 'milestones']);
    $router->get('/student/submissions', [StudentDashboardController::class, 'submissions']);
    $router->post('/student/submissions-upload', [StudentDashboardController::class, 'uploadSubmission']);
    $router->get('/student/submissions-file', [StudentDashboardController::class, 'serveSubmissionFile']);
    $router->get('/student/feedback', [StudentDashboardController::class, 'feedback']);
    $router->get('/student/consultations', [StudentDashboardController::class, 'consultations']);
    $router->post('/student/consultations-request', [StudentDashboardController::class, 'requestConsultation']);
});

// adviser routes
$router->group('auth:adviser', function (Router $router) {
    $router->get('/adviser/dashboard', [AdviserDashboardController::class, 'index']);
    $router->get('/adviser/groups', [AdviserDashboardController::class, 'groups']);
    $router->get('/adviser/milestones', [AdviserDashboardController::class, 'milestones']);
    $router->get('/adviser/submissions', [AdviserDashboardController::class, 'submissions']);
    $router->post('/adviser/submissions-update', [AdviserDashboardController::class, 'updateSubmission']);
    $router->get('/adviser/submissions-file', [AdviserDashboardController::class, 'serveSubmissionFile']);
    $router->get('/adviser/consultations', [AdviserDashboardController::class, 'consultations']);
});

// Use the stripped URI set by index.php (handles subdirectory installations)
$uri = $_SERVER['APP_REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($uri, $method);