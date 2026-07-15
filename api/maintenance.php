<?php

/**
 * Returns true if maintenance mode is currently enabled in system_settings.
 * Used to gate login and to power the public maintenance_status.php endpoint.
 */
function isMaintenanceModeOn(PDO $pdo): bool
{
    $stmt = $pdo->prepare(
        "SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode' LIMIT 1"
    );
    $stmt->execute();
    $row = $stmt->fetch();

    return $row && $row['setting_value'] === '1';
}
