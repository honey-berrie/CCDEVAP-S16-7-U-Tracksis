<?php

namespace App\Controllers\Adviser;

use App\Core\Controller;
use App\Models\Team;

class AdviserDashboardController extends Controller
{
    public function index(): void
    {
        $adviserId = $_SESSION['user_id'] ?? 0;

        $this->renderPlain('adviser/dashboard', [
            'userName' => $_SESSION['user_name'] ?? 'Adviser',
            'firstname' => $_SESSION['firstname'] ?? '',
            'lastname' => $_SESSION['lastname'] ?? '',
            'assignedGroups' => Team::getAdviserGroups($adviserId),
            'pendingReviews' => Team::getAdviserPendingReviews($adviserId),
            'upcomingConsultations' => Team::getAdviserUpcomingConsultations($adviserId),
            'unreadAlerts' => Team::getAdviserUnreadAlerts($adviserId),
            'groupProgress' => Team::getAdviserGroupProgress($adviserId),
            'recentActivities' => Team::getAdviserRecentActivities($adviserId),
        ]);
    }

    public function groups(): void
    {
        $adviserId = $_SESSION['user_id'] ?? 0;

        $this->renderPlain('adviser/groups', [
            'userName' => $_SESSION['user_name'] ?? 'Adviser',
            'firstname' => $_SESSION['firstname'] ?? '',
            'lastname' => $_SESSION['lastname'] ?? '',
            'groups' => Team::getAdviserGroupSummaries($adviserId),
        ]);
    }

    public function milestones(): void
    {
        $adviserId = $_SESSION['user_id'] ?? 0;

        $this->renderPlain('adviser/milestones', [
            'userName' => $_SESSION['user_name'] ?? 'Adviser',
            'firstname' => $_SESSION['firstname'] ?? '',
            'lastname' => $_SESSION['lastname'] ?? '',
            'groups' => Team::getAdviserMilestoneGroups($adviserId),
        ]);
    }

    public function submissions(): void
    {
        $adviserId = $_SESSION['user_id'] ?? 0;

        $this->renderPlain('adviser/submissions', [
            'userName' => $_SESSION['user_name'] ?? 'Adviser',
            'firstname' => $_SESSION['firstname'] ?? '',
            'lastname' => $_SESSION['lastname'] ?? '',
            'submissions' => Team::getAdviserSubmissions($adviserId),
        ]);
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

    public function consultations(): void
    {
        $adviserId = $_SESSION['user_id'] ?? 0;

        $consultations = Team::getAdviserConsultations($adviserId);

        // split into upcoming and history
        $upcoming = array_filter($consultations, fn($c) => in_array($c['status'], ['pending', 'approved']));
        $history = array_filter($consultations, fn($c) => in_array($c['status'], ['completed', 'cancelled']));

        $this->renderPlain('adviser/consultations', [
            'userName' => $_SESSION['user_name'] ?? 'Adviser',
            'firstname' => $_SESSION['firstname'] ?? '',
            'lastname' => $_SESSION['lastname'] ?? '',
            'upcoming' => array_values($upcoming),
            'history' => array_values($history),
        ]);
    }

    public function updateSubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $submissionId = (int) ($input['submission_id'] ?? 0);
        $status = $input['status'] ?? '';
        $feedback = trim($input['feedback'] ?? '');

        if ($submissionId <= 0 || !in_array($status, ['approved', 'rejected', 'revision-requested', 'in-review'])) {
            $this->json(['error' => 'Invalid submission data.'], 400);
            return;
        }

        $ok = Team::updateSubmissionStatus($submissionId, $status, $feedback);

        if ($ok) {
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Failed to update submission status.'], 500);
        }
    }
}