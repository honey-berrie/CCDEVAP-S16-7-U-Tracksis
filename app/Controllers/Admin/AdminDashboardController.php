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

    public function sidebarComponent(): void
    {
        require __DIR__ . '/../../Components/Admin/adm-sidebar.php';
    }

    public function dashboardData(): void
    {
        require __DIR__ . '/adm-dashboard-data.php';
    }

    public function usersData(): void
    {
        require __DIR__ . '/adm-users-data.php';
    }

    public function usersActions(): void
    {
        require __DIR__ . '/adm-users-actions.php';
    }

    public function userStatsData(): void
    {
        require __DIR__ . '/adm-user-stats.php';
    }

    public function recordsData(): void
    {
        require __DIR__ . '/adm-records-data.php';
    }

    public function announcementsData(): void
    {
        require __DIR__ . '/adm-announcements-data.php';
    }

    public function announcementsActions(): void
    {
        require __DIR__ . '/adm-announcements-actions.php';
    }

    public function archiveData(): void
    {
        require __DIR__ . '/adm-archive-data.php';
    }

    public function archiveActions(): void
    {
        require __DIR__ . '/adm-archive-actions.php';
    }

    public function analyticsData(): void
    {
        require __DIR__ . '/adm-analytics-data.php';
    }

    public function feedbackData(): void
    {
        require __DIR__ . '/adm-feedback-data.php';
    }

    public function filesData(): void
    {
        require __DIR__ . '/adm-files-data.php';
    }

    public function settingsData(): void
    {
        require __DIR__ . '/adm-settings-data.php';
    }

    public function settingsActions(): void
    {
        require __DIR__ . '/adm-settings-actions.php';
    }
}