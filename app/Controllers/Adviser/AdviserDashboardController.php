<?php

namespace App\Controllers\Adviser;

use App\Core\Controller;
use App\Models\Team;

class AdviserDashboardController extends Controller
{
    public function index(): void
    {
        $userName = $_SESSION['user_name'] ?? 'Adviser';
        $firstname = $_SESSION['firstname'] ?? '';
        $lastname = $_SESSION['lastname'] ?? '';
        $adviserId = $_SESSION['user_id'] ?? 0;

        // fetch adviser's assigned groups
        $assignedGroups = Team::getAdviserGroups($adviserId);

        // fetch pending reviews (submissions in-review for adviser's groups)
        $pendingReviews = Team::getAdviserPendingReviews($adviserId);

        // fetch upcoming consultations where adviser is recipient
        $upcomingConsultations = Team::getAdviserUpcomingConsultations($adviserId);

        // fetch unread notifications count
        $unreadAlerts = Team::getAdviserUnreadAlerts($adviserId);

        // fetch group progress data
        $groupProgress = Team::getAdviserGroupProgress($adviserId);

        // fetch recent activity
        $recentActivities = Team::getAdviserRecentActivities($adviserId);

        $this->renderPlain('adviser/dashboard', [
            'userName' => $userName,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'assignedGroups' => $assignedGroups,
            'pendingReviews' => $pendingReviews,
            'upcomingConsultations' => $upcomingConsultations,
            'unreadAlerts' => $unreadAlerts,
            'groupProgress' => $groupProgress,
            'recentActivities' => $recentActivities,
        ]);
    }
}