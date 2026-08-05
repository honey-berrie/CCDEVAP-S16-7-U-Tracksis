<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class AdminDashboardController extends Controller
{
    public function index(): void
    {
        $this->renderPlain('Admin/adm-dashboard');
    }

    public function users(): void
    {
        $this->renderPlain('Admin/adm-users');
    }

    public function records(): void
    {
        $this->renderPlain('Admin/adm-records');
    }

    public function announcements(): void
    {
        $this->renderPlain('Admin/adm-announcements');
    }

    public function analytics(): void
    {
        $this->renderPlain('Admin/adm-analytics');
    }

    public function archive(): void
    {
        $this->renderPlain('Admin/adm-archive');
    }

    public function feedback(): void
    {
        $this->renderPlain('Admin/adm-feedback');
    }

    public function files(): void
    {
        $this->renderPlain('Admin/adm-files');
    }

    public function settings(): void
    {
        $this->renderPlain('Admin/adm-settings');
    }
}