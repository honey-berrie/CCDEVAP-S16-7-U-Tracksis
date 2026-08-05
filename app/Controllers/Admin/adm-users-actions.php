<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();

require_once __DIR__ . "/../../Models/Admin/adm-user-model.php";

use App\Models\Admin\UserModel;

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Only POST is allowed on this endpoint."]);
    exit;
}

$userModel = new UserModel($conn);
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

        if ($userModel->emailExists($email)) {
            fail("An account with that email already exists.");
        }

        $id = $userModel->createUser($role, $firstname, $lastname, $email, $password);

        if ($id === false) {
            fail("Could not create the account: " . $conn->error, 500);
        }

        ok(["id" => $id]);
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

        if ($userModel->emailExists($email, $id)) {
            fail("Another account already uses that email.");
        }

        if (!$userModel->updateUser($id, $firstname, $lastname, $email, $role, $isActive)) {
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

        if (!$userModel->deleteUser($id)) {
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

        try {
            $teamId = $userModel->createTeamWithMembers(
                $groupName,
                $thesisTitle,
                $abstract,
                $adviserId,
                $academicYear,
                $defenseDate,
                $memberIds
            );
            ok(["id" => $teamId]);
        } catch (Exception $e) {
            fail("Could not create the thesis group: " . $e->getMessage(), 500);
        }
        break;
    }

    default:
        fail("Unknown action.");
}
