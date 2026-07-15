<?php

function json($data, int $status = 200): void {
  header('Content-Type: application/json; charset=utf-8');
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_SLASHES);
  exit;
}

function error(string $msg, int $status = 400, array $extra = []): void {
  json(array_merge(['error' => $msg], $extra), $status);
}

function getInput(): array {
  if (!empty($_POST)) return $_POST;
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function currentUser(): ?array {
  return $_SESSION['user'] ?? null;
}

function requireLogin(): array {
  $u = currentUser();
  if (!$u) error('Not logged in', 401);
  return $u;
}

function requireRole(array $roles): array {
  $u = requireLogin();
  if (!in_array($u['role'], $roles, true)) error('Forbidden', 403);
  return $u;
}

function initialsOf($first, $last) {
    $f = $first ? substr($first, 0, 1) : '';
    $l = $last ? substr($last, 0, 1)  : '';

    return strtoupper(($f . $l) ?: 'SY');
}

function statusLabel($status) {
    return [
        'pending'   => 'Pending',
        'in-progress' => 'In Progress',
        'in-review' => 'In Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ][$status];
}

function statusClass($status) {
    return [
        'pending'   => 'badge-progress',
        'in-progress' => 'badge-progress',
        'in-review' => 'badge-review',
        'approved' => 'badge-approved',
        'rejected' => 'badge-rejected',
    ][$status] ?? 'badge-pending';
}

function timeAgo( $ts) {
    $diff = time() - $ts;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';

    return date('M j, Y', $ts);
}

function iconForType($type){
    return [
        'submission_uploaded' => 'cloud-upload-fill',
        'submission_reviewed' => 'check-circle-fill',
        'milestone_approved' => 'flag-fill',
        'milestone_updated' => 'flag-fill',
        'feedback_added' => 'chat-left-text-fill',
        'consultation_requested' => 'chat-dots-fill',
        'consultation_approved' => 'calendar-check',
        'announcement_posted' => 'megaphone-fill'
    ][$type] ?? 'activity';
}

?>