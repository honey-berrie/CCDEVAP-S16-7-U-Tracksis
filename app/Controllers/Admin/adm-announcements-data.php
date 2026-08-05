<?php

require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminApi();

require_once __DIR__ . "/../../Models/Admin/adm-announcement-model.php";

use App\Models\Admin\AnnouncementModel;

$announcementModel = new AnnouncementModel($conn);

header("Content-Type: application/json");
echo json_encode(["announcements" => $announcementModel->getAll()]);
