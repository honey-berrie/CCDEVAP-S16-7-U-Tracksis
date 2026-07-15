<?php

require_once "session.php";
requireAdminApi();

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Only POST is allowed on this endpoint."]);
    exit;
}

$action = $_POST['action'] ?? "";

function fail($message, $code = 400)
{
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit;
}

function ok($data = [])
{
    echo json_encode(array_merge(["success" => true], $data));
    exit;
}

switch ($action) {

    case "create_user": {
        $firstname = trim($_POST['firstname'] ?? "");
        $lastname  = trim($_POST['lastname'] ?? "");
        $email     = trim($_POST['email'] ?? "");
        $role      = trim($_POST['role'] ?? "");
        $password  = $_POST['password'] ?? "";

        if ($firstname === "" || $lastname === "" || $email === "" || $password === "") {
            fail("First name, last name, email, and password are required.");
        }
        if (!in_array($role, ['student', 'adviser', 'coordinator', 'admin'], true)) {
            fail("Invalid role.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            fail("Invalid email address.");
        }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            fail("An account with that email already exists.");
        }

        // Password hashing is not required for this phase, per project scope.
        $stmt = $conn->prepare(
            "INSERT INTO users (role, firstname, lastname, email, password_hash)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $role, $firstname, $lastname, $email, $password);

        if (!$stmt->execute()) {
            fail("Could not create the account: " . $conn->error, 500);
        }

        ok(["id" => $stmt->insert_id]);
        break;
    }

    case "update_user": {
        $id        = (int) ($_POST['id'] ?? 0);
        $firstname = trim($_POST['firstname'] ?? "");
        $lastname  = trim($_POST['lastname'] ?? "");
        $email     = trim($_POST['email'] ?? "");
        $role      = trim($_POST['role'] ?? "");
        $isActive  = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1;

        if ($id <= 0 || $firstname === "" || $lastname === "" || $email === "") {
            fail("Missing required fields.");
        }
        if (!in_array($role, ['student', 'adviser', 'coordinator', 'admin'], true)) {
            fail("Invalid role.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            fail("Invalid email address.");
        }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            fail("Another account already uses that email.");
        }

        $stmt = $conn->prepare(
            "UPDATE users SET firstname=?, lastname=?, email=?, role=?, is_active=?
             WHERE id=?"
        );
        $stmt->bind_param("ssssii", $firstname, $lastname, $email, $role, $isActive, $id);

        if (!$stmt->execute()) {
            fail("Could not update the account: " . $conn->error, 500);
        }

        ok();
        break;
    }

    case "delete_user": {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            fail("Missing user id.");
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            fail("Could not delete the account: " . $conn->error, 500);
        }

        ok();
        break;
    }

    case "create_team": {
        $groupName    = trim($_POST['group_name'] ?? "");
        $thesisTitle  = trim($_POST['thesis_title'] ?? "");
        $abstract     = trim($_POST['abstract'] ?? "");
        $adviserId    = !empty($_POST['adviser_id']) ? (int) $_POST['adviser_id'] : null;
        $academicYear = trim($_POST['academic_year'] ?? "");
        $defenseDate  = !empty($_POST['defense_date']) ? $_POST['defense_date'] : null;
        $memberIds    = $_POST['member_ids'] ?? [];

        if ($groupName === "" || $thesisTitle === "") {
            fail("Group name and thesis title are required.");
        }
        if (!is_array($memberIds)) {
            $memberIds = [];
        }

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare(
                "INSERT INTO teams (group_name, thesis_title, abstract, adviser_id, academic_year, defense_date)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssiss",
                $groupName,
                $thesisTitle,
                $abstract,
                $adviserId,
                $academicYear,
                $defenseDate
            );
            $stmt->execute();
            $teamId = $stmt->insert_id;

            if (count($memberIds) > 0) {
                $memberStmt = $conn->prepare(
                    "INSERT INTO group_members (group_id, user_id, member_role) VALUES (?, ?, 'Member')"
                );
                foreach ($memberIds as $studentId) {
                    $studentId = (int) $studentId;
                    if ($studentId > 0) {
                        $memberStmt->bind_param("ii", $teamId, $studentId);
                        $memberStmt->execute();
                    }
                }
            }

            $conn->commit();
            ok(["id" => $teamId]);
        } catch (Exception $e) {
            $conn->rollback();
            fail("Could not create the thesis group: " . $e->getMessage(), 500);
        }
        break;
    }

    default:
        fail("Unknown action.");
}
