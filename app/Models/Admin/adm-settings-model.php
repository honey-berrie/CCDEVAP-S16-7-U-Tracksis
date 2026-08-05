<?php

namespace App\Models\Admin;

class SettingsModel
{
    private \mysqli $conn;

    public function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getSystemSettings(): array
    {
        $settings = [];
        $result = $this->conn->query("SELECT setting_key, setting_value FROM system_settings");
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function updateProfile(int $id, string $firstname, string $lastname, string $email, string $password = ""): bool
    {
        if ($password !== "") {
            $stmt = $this->conn->prepare(
                "UPDATE users SET firstname=?, lastname=?, email=?, password_hash=? WHERE id=?"
            );
            $stmt->bind_param("ssssi", $firstname, $lastname, $email, $password, $id);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE users SET firstname=?, lastname=?, email=? WHERE id=?"
            );
            $stmt->bind_param("sssi", $firstname, $lastname, $email, $id);
        }

        return $stmt->execute();
    }

    public function updateMaintenanceMode(bool $enabled): bool
    {
        $maintenance = $enabled ? "1" : "0";

        $stmt = $this->conn->prepare(
            "INSERT INTO system_settings (setting_key, setting_value) VALUES ('maintenance_mode', ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        $stmt->bind_param("s", $maintenance);

        return $stmt->execute();
    }
}
