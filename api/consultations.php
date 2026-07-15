<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mailer.php';

$user = requireLogin();
$method = $_SERVER['REQUEST_METHOD'];

$userId = $user['id'];

// Get user's group
$g = $pdo->prepare("SELECT group_id FROM group_members WHERE user_id = ? LIMIT 1");
$g->execute([$userId]);
$gid = $g->fetchColumn();

if (!$gid) {
    json(['items' => [], 'total' => 0]);
}

// list consultations
if ($method === 'GET') {

    // consultations for this group
    $stmt = $pdo->prepare(
        "SELECT c.id, c.group_id, c.user_id, c.recipient_id, c.topic, c.agenda, c.proposed_schedule, c.status, c.adviser_notes, c.created_at, c.updated_at,
                u_req.firstname AS requester_firstname, u_req.lastname AS requester_lastname,
                u_rec.firstname AS recipient_firstname, u_rec.lastname AS recipient_lastname,
                u_rec.role AS recipient_role
         FROM consultations c
         JOIN users u_req ON u_req.id = c.user_id
         JOIN users u_rec ON u_rec.id = c.recipient_id
         WHERE c.group_id = ?
         ORDER BY c.created_at DESC"
    );
    $stmt->execute([$gid]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$r) {
        $r['requester_name'] = trim(($r['requester_firstname'] ?? '') . ' ' . ($r['requester_lastname'] ?? ''));
        $r['recipient_name'] = trim(($r['recipient_firstname'] ?? '') . ' ' . ($r['recipient_lastname'] ?? ''));
        $r['recipient_initials'] = initialsOf($r['recipient_firstname'] ?? '', $r['recipient_lastname'] ?? '');
        $r['status_label'] = statusLabel($r['status']);
        $r['status_class'] = statusClass($r['status']);
        $r['date_formatted'] = $r['proposed_schedule'] ? date('M j, Y', strtotime($r['proposed_schedule'])) : null;
        $r['time_formatted'] = $r['proposed_schedule'] ? date('g:i A', strtotime($r['proposed_schedule'])) : null;
        unset($r['requester_firstname'], $r['requester_lastname'], $r['recipient_firstname'], $r['recipient_lastname']);
    }
    unset($r);

    // available recipients
    $recStmt = $pdo->prepare(
        "SELECT u.id, u.firstname, u.lastname, u.role, u.avatar_url
         FROM users u
         WHERE u.role IN ('adviser', 'coordinator', 'admin') AND u.is_active = 1
         ORDER BY u.role, u.lastname"
    );
    $recStmt->execute();
    $recipients = $recStmt->fetchAll();

    foreach ($recipients as &$rec){
        $rec['name'] = trim(($rec['firstname'] ?? '') . ' ' . ($rec['lastname'] ?? ''));
        $rec['initials'] = initialsOf($rec['firstname'] ?? '', $rec['lastname'] ?? '');
        unset($rec['firstname'], $rec['lastname']);
    }
    unset($rec);

    json([
        'items' => $rows,
        'total' => count($rows),
        'recipients' => $recipients,
    ]);
}

// send consultation request
if ($method === 'POST') {

    $in = getInput();

    $recipientId = !empty($in['recipient_id']) ? (int) $in['recipient_id'] : null;
    $topic = trim($in['topic'] ?? '');
    $agenda = trim($in['agenda'] ?? '');
    $proposedSchedule = trim($in['proposed_schedule'] ?? '');

    if (!$recipientId || !$topic) {
        error('Recipient and topic are required', 422);
    }

    if (!$proposedSchedule) {
        error('You must select a date and time', 422);
    }

    // verify recipient exists and is adviser, coord or admin
    $check = $pdo->prepare("SELECT id, role, email, firstname, lastname FROM users WHERE id = ? AND is_active = 1");
    $check->execute([$recipientId]);
    $recipient = $check->fetch();

    if (!$recipient) {
        error('Recipient not found', 404);
    }

    $slotValue = $proposedSchedule ?: null;

    $ins = $pdo->prepare(
        "INSERT INTO consultations (group_id, user_id, recipient_id, topic, agenda, proposed_schedule)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $ins->execute([$gid, $userId, $recipientId, $topic, $agenda ?: null, $slotValue]);
    $newId = $pdo->lastInsertId();

    // log activity
    $act = $pdo->prepare(
        "INSERT INTO activities (group_id, user_id, type, description) VALUES (?, ?, 'consultation_requested', ?)"
    );
    $actDesc = $user['firstname'] . ' ' . $user['lastname'] . ' requested consultation: ' . $topic;
    $act->execute([$gid, $userId, $actDesc]);

    // send email notification to recipient
    sendConsultationEmail($recipient, $user, $topic, $agenda, $proposedSchedule);

    json([
        'message' => 'Consultation request sent',
        'id' => (int) $newId,
    ], 201);
}

function sendConsultationEmail($recipient, $requester, $topic, $agenda, $schedule) {

    $to = $recipient['email'];
    $recipientName = trim(($recipient['firstname'] ?? '') . ' ' . ($recipient['lastname'] ?? ''));
    $requesterName = trim(($requester['firstname'] ?? '') . ' ' . ($requester['lastname'] ?? ''));

    $subject = 'New Consultation Request: ' . $topic;

    $scheduleLine = date('l, F j, Y \a\t g:i A', strtotime($schedule));

    $bodyHtml = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #2563eb;'>New Consultation Request</h2>
        <p>Hi <strong>{$recipientName}</strong>,</p>
        <p>You have received a new consultation request from <strong>{$requesterName}</strong>.</p>
        <table style='border-collapse: collapse; width: 100%; margin: 15px 0;'>
            <tr><td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Topic</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>{$topic}</td></tr>
            <tr><td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Schedule</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>{$scheduleLine}</td></tr>
            <tr><td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Agenda</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>" . ($agenda ?: 'None provided') . "</td></tr>
        </table>
        <p>Please log in to <strong>U-Tracksis</strong> to approve or manage this request.</p>
        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
        <p style='color: #888; font-size: 12px;'>This is an automated message from U-Tracksis System.</p>
    </div>";

    $bodyAlt = "Hi {$recipientName},\n\n"
        . "You have received a new consultation request from {$requesterName}.\n\n"
        . "Topic: {$topic}\n"
        . "Schedule: {$scheduleLine}\n"
        . "Agenda: " . ($agenda ?: 'None provided') . "\n\n"
        . "Please log in to U-Tracksis to approve or manage this request.\n\n"
        . "- U-Tracksis System";

    sendEmail($to, $recipientName, $subject, $bodyHtml, $bodyAlt);
}
