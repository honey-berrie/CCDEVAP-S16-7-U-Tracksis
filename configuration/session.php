<?php
/*
|--------------------------------------------------------------------------
| Session Helper
|--------------------------------------------------------------------------
| This project does not implement login/session management yet (per the
| Phase 2 requirements, auth is out of scope for this phase). This helper
| exists so every Admin page can still pull "the logged-in user" from a
| single place instead of hardcoding a name anywhere.
|
| If a real login page is added later, it only needs to set:
|   $_SESSION['user_id'] = <users.id>
| and every page using getCurrentAdmin() will pick it up automatically.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/connect.php";

/**
 * Returns the currently logged-in admin as an associative array
 * (id, role, firstname, lastname, email, avatar_url).
 *
 * Falls back to the first active admin account in the database when no
 * session is present, so the name shown is always real data, never a
 * hardcoded string.
 */
function getCurrentAdmin($conn)
{
    if (!empty($_SESSION['user_id'])) {
        $userId = (int) $_SESSION['user_id'];
        $stmt = $conn->prepare(
            "SELECT id, role, firstname, lastname, email, avatar_url
             FROM users
             WHERE id = ? AND role = 'admin' AND is_active = 1
             LIMIT 1"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
    }

    // Fallback: first active admin on record.
    $result = $conn->query(
        "SELECT id, role, firstname, lastname, email, avatar_url
         FROM users
         WHERE role = 'admin' AND is_active = 1
         ORDER BY id ASC
         LIMIT 1"
    );

    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }

    // No admin exists in the database at all.
    return [
        "id" => null,
        "role" => "admin",
        "firstname" => "Admin",
        "lastname" => "",
        "email" => null,
        "avatar_url" => null,
    ];
}
