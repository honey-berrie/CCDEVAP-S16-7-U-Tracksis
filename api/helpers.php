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

?>