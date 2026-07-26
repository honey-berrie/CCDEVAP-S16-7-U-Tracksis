<?php

namespace App\Controllers\Test;

use App\Core\Controller;


class DashboardController extends Controller
{
    // render the dashboard page
    public function index(): void
    {
        $userName = $_SESSION['user_name'] ?? 'Username failed to retrieve!';
        $role = $_SESSION['user_role'] ?? 'Role failed to retrieve!';

        $this->renderPlain('test/dashboard', [
            'userName' => $userName,
            'role' => $role
        ]);
    }

}