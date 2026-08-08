<?php

namespace App\Controllers\Student;

use App\Core\Controller;
use App\Models\User;
use App\Models\Team;
use App\Models\Adviser;

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

    public function groupProfile(): void
    {
        $userName = $_SESSION['user_name'] ?? 'Username failed to retrieve!';
        $firstname = $_SESSION['firstname'] ?? '<Failed to retrieve>';
        $lastname = $_SESSION['lastname'] ?? '<Failed to retrieve>';

        $userGroupId = User::getUserGroupId($_SESSION['user_id']);

        if (!$userGroupId) {
            $this->renderPlain('student/group-profile', [
                'userName' => $userName,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'teamInfo' => null,
                'members' => [],
                'adviser' => null,
                'flash' => null,
            ]);
            return;
        }

        $teamInfo = Team::getTeamInfo($userGroupId['group_id']);
        $members = Team::getTeamMembers($userGroupId['group_id']);
        $adviser = Team::getTeamAdviser($userGroupId['group_id']);

        $flash = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        echo "<script>console.log('Team Info:', " . json_encode($teamInfo) . ");</script>";
        echo "<script>console.log('Members:', " . json_encode($members) . ");</script>";
        echo "<script>console.log('Adviser:', " . json_encode($adviser) . ");</script>";

        $this->renderPlain('student/group-profile', [
            'userName' => $userName,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'teamInfo' => $teamInfo,
            'members' => $members,
            'adviser' => $adviser,
            'flash' => $flash,
        ]);
    }

    public function updateGroupProfile(): void
    {
        $userGroupId = User::getUserGroupId($_SESSION['user_id']);

        if (!$userGroupId) {
            $this->redirect('/student/group-profile');
            return;
        }

        $groupName = trim($_POST['team_name'] ?? '');
        $thesisTitle = trim($_POST['thesis_title'] ?? '');
        $abstract = trim($_POST['abstract'] ?? '');

        if ($groupName === '' || $thesisTitle === '' || $abstract === '') {
            $_SESSION['error_message'] = 'All fields are required.';
            $this->redirect('/student/group-profile');
            return;
        }

        $result = Team::updateTeamInfo($userGroupId['group_id'], $groupName, $thesisTitle, $abstract);

        if ($result) {
            $_SESSION['success_message'] = 'Group profile updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to update group profile.';
        }
        $this->redirect('/student/group-profile');
    }

    public function milestones(): void
    {
        $userName = $_SESSION['user_name'] ?? 'Username failed to retrieve!';
        $firstname = $_SESSION['firstname'] ?? '<Failed to retrieve>';
        $lastname = $_SESSION['lastname'] ?? '<Failed to retrieve>';

        $userGroupId = User::getUserGroupId($_SESSION['user_id']);

        if (!$userGroupId) {
            $this->renderPlain('student/milestones', [
                'userName' => $userName,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'milestones' => [],
                'total_count' => 0,
            ]);
            return;
        }

        $teamData = Team::getTeamMilestones($userGroupId['group_id']);

        echo "<script>console.log('Team Data:', " . json_encode($teamData) . ");</script>";

        $this->renderPlain('student/milestones', [
            'userName' => $userName,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'milestones' => $teamData['milestones'],
            'total_count' => $teamData['total_count'],
        ]);
    }

    public function submissions(): void
    {
        $userName = $_SESSION['user_name'] ?? 'Username failed to retrieve!';
        $firstname = $_SESSION['firstname'] ?? '<Failed to retrieve>';
        $lastname = $_SESSION['lastname'] ?? '<Failed to retrieve>';

        $userGroupId = User::getUserGroupId($_SESSION['user_id']);

        if (!$userGroupId) {
            $this->renderPlain('student/submissions', [
                'userName' => $userName,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'submissions' => [],
            ]);
            return;
        }

        $submissions = Team::getTeamSubmissions($userGroupId['group_id']);

        echo "<script>console.log('Submissions:', " . json_encode($submissions) . ");</script>";

        $this->renderPlain('student/submissions', [
            'userName' => $userName,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'submissions' => $submissions,
        ]);

    }

    public function uploadSubmission(): void
    {
        $userGroupId = User::getUserGroupId($_SESSION['user_id']);

        if (!$userGroupId) {
            $_SESSION['error_message'] = 'You are not part of any group.';
            $this->redirect('/student/submissions');
            return;
        }

        $documentType = trim($_POST['docType'] ?? '');
        $file = $_FILES['submissionFile'] ?? null;

        if ($documentType === '' || !$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = 'Please select a document type and a PDF file.';
            $this->redirect('/student/submissions');
            return;
        }

        $result = Team::uploadSubmission($userGroupId['group_id'], $documentType, $file, $_SESSION['user_id']);

        if ($result === true) {
            $_SESSION['success_message'] = 'Submission uploaded successfully!';
        } elseif ($result === 'not_pdf') {
            $_SESSION['error_message'] = 'Only PDF files are accepted.';
        } elseif ($result === 'too_large') {
            $_SESSION['error_message'] = 'File exceeds the 25MB size limit.';
        } else {
            $_SESSION['error_message'] = 'Failed to upload submission. Please try again.';
        }
        $this->redirect('/student/submissions');
    }

    public function serveSubmissionFile(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $download = !empty($_GET['download']);

        if (!$id) {
            http_response_code(422);
            echo 'Missing file ID';
            return;
        }

        $file = Team::getSubmissionFile($id);

        if (!$file) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        $path = __DIR__ . '/../../../uploads/' . $file['file_path'];
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'File missing on disk';
            return;
        }

        $disposition = $download ? 'attachment' : 'inline';

        header('Content-Type: ' . ($file['mime_type'] ?: 'application/pdf'));
        header('Content-Disposition: ' . $disposition . '; filename="' . basename($file['file_name']) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=3600');
        readfile($path);
        exit;
    }

    public function feedback(): void
    {
        $userName = $_SESSION['user_name'] ?? 'Username failed to retrieve!';
        $firstname = $_SESSION['firstname'] ?? '<Failed to retrieve>';
        $lastname = $_SESSION['lastname'] ?? '<Failed to retrieve>';

        $userGroupId = User::getUserGroupId($_SESSION['user_id']);

        if (!$userGroupId) {
            $this->renderPlain('student/feedback', [
                'userName' => $userName,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'feedbacks' => [],
            ]);
            return;
        }

        $feedbacks = Team::getTeamFeedback($userGroupId['group_id']);

        echo "<script>console.log('Feedbacks:', " . json_encode($feedbacks) . ");</script>";

        $this->renderPlain('student/feedback', [
            'userName' => $userName,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'feedbacks' => $feedbacks,
        ]);
    }

    public function consultations(): void
    {
        $userName = $_SESSION['user_name'] ?? 'Username failed to retrieve!';
        $firstname = $_SESSION['firstname'] ?? '<Failed to retrieve>';
        $lastname = $_SESSION['lastname'] ?? '<Failed to retrieve>';

        $userGroupId = User::getUserGroupId($_SESSION['user_id']);

        if (!$userGroupId) {
            $this->renderPlain('student/consultations', [
                'userName' => $userName,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'consultations' => [],
            ]);
            return;
        }

        $consultations = Team::getTeamConsultations($userGroupId['group_id']);
        $availableAdvisers = Adviser::getAllAdvisers();

        echo "<script>console.log('Consultations:', " . json_encode($consultations) . ");</script>";
        echo "<script>console.log('Available Advisers:', " . json_encode($availableAdvisers) . ");</script>";

        $this->renderPlain('student/consultations', [
            'userName' => $userName,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'consultations' => $consultations,
            'availableAdvisers' => $availableAdvisers,
        ]);
    }

    public function requestConsultation(): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            $this->redirect('/logout');
            return;
        }

        $userGroupId = User::getUserGroupId($userId);

        if (!$userGroupId) {
            $this->json(['error' => 'You are not part of any group.'], 422);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $recipientId = (int) ($input['recipient_id'] ?? 0);
        $topic = trim($input['topic'] ?? '');
        $agenda = trim($input['agenda'] ?? '');
        $date = trim($input['date'] ?? '');
        $time = trim($input['time'] ?? '');

        if (!$recipientId || $topic === '') {
            $this->json(['error' => 'Please select a recipient and enter a topic.'], 422);
            return;
        }

        $proposedSchedule = null;
        if ($date !== '') {
            $proposedSchedule = $date . ' ' . ($time !== '' ? $time . ':00' : '00:00:00');
        }

        $consultation = Team::createConsultationRequest(
            $userGroupId['group_id'],
            $userId,
            $recipientId,
            $topic,
            $agenda,
            $proposedSchedule
        );

        if ($consultation) {
            $this->json(['success' => true, 'consultation' => $consultation]);
        } else {
            $this->json(['error' => 'Failed to send the consultation request. Please try again.'], 500);
        }
    }

    public function announcements(): void
    {
        $userName = $_SESSION['user_name'] ?? 'Username failed to retrieve!';
        $firstname = $_SESSION['firstname'] ?? '<Failed to retrieve>';
        $lastname = $_SESSION['lastname'] ?? '<Failed to retrieve>';

        $announcements = User::getGlobalAnnouncements($_SESSION['user_id']);

        $this->renderPlain('student/announcements', [
            'userName' => $userName,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'announcements' => $announcements,
        ]);
    }

    public function markAnnouncementRead(): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $this->json(['error' => 'Not logged in'], 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $announcementId = (int) ($input['id'] ?? 0);

        if (!$announcementId) {
            $this->json(['error' => 'Missing announcement id'], 422);
            return;
        }

        $ok = User::markAnnouncementAsRead($userId, $announcementId);
        $this->json(['success' => $ok]);
    }
}
