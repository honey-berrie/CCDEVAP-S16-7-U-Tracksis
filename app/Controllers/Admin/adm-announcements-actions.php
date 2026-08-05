<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();
header("Content-Type: application/json");

require_once __DIR__ . "/../../Models/Admin/adm-announcement-model.php";

use App\Models\Admin\AnnouncementModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Only POST is allowed on this endpoint."]);
    exit;
}

$announcementModel = new AnnouncementModel($conn);
$action = $_POST['action'] ?? "";
$admin = getCurrentAdmin($conn);

function fail($message, $code = 400)
{
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit;
}

switch ($action) {

    case "save": {
        $id      = (int) ($_POST['id'] ?? 0);
        $title   = trim($_POST['title'] ?? "");
        $message = trim($_POST['message'] ?? "");

        if ($title === "" || $message === "") {
            fail("Title and message are required.");
        }

        if ($id > 0) {
            if (!$announcementModel->update($id, $title, $message)) {
                fail("Could not save this announcement: " . $conn->error, 500);
            }
            echo json_encode(["success" => true, "id" => $id]);
        } else {
            $authorId = $admin['id'];
            if (!$authorId) {
                fail("No admin account found to attribute this announcement to.", 500);
            }

            $newId = $announcementModel->insert($authorId, $title, $message);
            if ($newId === false) {
                fail("Could not save this announcement: " . $conn->error, 500);
            }
            echo json_encode(["success" => true, "id" => $newId]);
        }
        break;
    }

    case "delete": {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) fail("Missing announcement id.");

        $deleted = $announcementModel->delete($id);

        echo json_encode(["success" => $deleted]);
        break;
    }

    default:
        fail("Unknown action.");
}
