<?php
require_once __DIR__ . "/../../Configuration/Admin/session.php";
requireAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../CSS/Admin/adm-links.css">
    <link rel="stylesheet" href="../../CSS/Admin/adm-settings.css">
</head>
<body>
    <div id="sidebar_placeholder"></div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Settings</h1>
                <p class="page-subtitle">Manage your profile and system-wide configuration.</p>
            </div>
        </div>

        <div class="settings-layout">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Profile</h2>
                </div>
                <form id="profileForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileFirstname">First Name</label>
                            <input type="text" class="form-control-admin" id="profileFirstname" required>
                        </div>
                        <div class="form-group">
                            <label for="profileLastname">Last Name</label>
                            <input type="text" class="form-control-admin" id="profileLastname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="profileEmail">Email</label>
                        <input type="email" class="form-control-admin" id="profileEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="profilePassword">Password</label>
                        <input type="password" class="form-control-admin" id="profilePassword" placeholder="Leave blank to keep your current password">
                    </div>
                    <p class="form-error" id="profileFormError"></p>
                    <button type="button" class="btn-primary-admin" id="saveProfileBtn">Save Profile</button>
                    <span class="save-confirm" id="profileSaveConfirm"></span>
                </form>
            </div>

            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Maintenance Mode</h2>
                </div>
                <p class="panel-subtitle">When enabled, students and advisers see a maintenance notice instead of the app.</p>
                <form id="maintenanceForm">
                    <div class="form-group pin-toggle">
                        <label>
                            <input type="checkbox" id="maintenanceMode">
                            Enable maintenance mode
                        </label>
                    </div>
                    <p class="form-error" id="maintenanceFormError"></p>
                    <button type="button" class="btn-primary-admin" id="saveMaintenanceBtn">Save</button>
                    <span class="save-confirm" id="maintenanceSaveConfirm"></span>
                </form>
            </div>
        </div>
    </div>

    <script src="../../JS/Admin/adm-global.js"></script>
    <script src="../../JS/Admin/adm-settings.js"></script>
</body>
</html>
