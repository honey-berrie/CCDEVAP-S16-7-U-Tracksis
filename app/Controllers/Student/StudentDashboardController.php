<?php

namespace App\Controllers\Student;

use App\Core\Controller;
use App\Models\User;
use App\Models\Team;

class StudentDashboardController extends Controller
{
    // render the dashboard page
    public function index(): void
    {
        $userName = $_SESSION['user_name'] ?? 'Username failed to retrieve!';
        $firstname = $_SESSION['firstname'] ?? '<Failed to retrieve>';
        $lastname = $_SESSION['lastname'] ?? '<Failed to retrieve>';
        //$role = $_SESSION['user_role'] ?? 'Role failed to retrieve!';

        $userGroupId = User::getUserGroupId($_SESSION['user_id']);

        if (!$userGroupId) {
            $this->renderPlain('student/dashboard', [
                'userName' => $userName,
                'firstname' => $firstname,
                'lastname' => $lastname,
                //'role' => $role
                'milestones' => [],
                'overall_progress' => 0,
                'done_count' => 0,
                'total_count' => 0,
                'next_deadline_days' => null,
                'defense_date' => null,
                'days_to_defense' => null,
                'latestFeedback' => [],
                'recentActivities' => [],
            ]);
            return;
        }

        $teamData = Team::getTeamMilestones($userGroupId['group_id']);

        $teamDefenseInfoData = Team::getTeamDefenseInfo($userGroupId['group_id']);

        $latestFeedback = Team::getLatestFeedback($userGroupId['group_id']);

        $recentActivities = Team::getRecentActivities($userGroupId['group_id']);

        echo "<script>console.log('Team Data:', " . json_encode($teamData) . ");</script>";
        echo "<script>console.log('Team Defense Info Data:', " . json_encode($teamDefenseInfoData) . ");</script>";
        echo "<script>console.log('Latest Feedback:', " . json_encode($latestFeedback) . ");</script>";
        echo "<script>console.log('Recent Activities:', " . json_encode($recentActivities) . ");</script>";

        $this->renderPlain('student/dashboard', [
            'userName' => $userName,
            'firstname' => $firstname,
            'lastname' => $lastname,
            //'role' => $role
            'milestones' => $teamData['milestones'],
            'overall_progress' => $teamData['overall_progress'],
            'done_count' => $teamData['done_count'],
            'total_count' => $teamData['total_count'],
            'next_deadline_days' => $teamData['next_deadline_days'],
            'defense_date' => $teamDefenseInfoData['date_formatted'],
            'days_to_defense' => $teamDefenseInfoData['days_to_defense'],
            'latestFeedback' => $latestFeedback,
            'recentActivities' => $recentActivities,
        ]);
    }

}