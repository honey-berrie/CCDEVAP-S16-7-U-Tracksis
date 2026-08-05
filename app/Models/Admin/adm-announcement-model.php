<?php

namespace App\Models\Admin;

class AnnouncementModel
{
    private \mysqli $conn;

    public function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(): array
    {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.title, a.message, a.created_at,
                   CONCAT(u.firstname, ' ', u.lastname) author
            FROM announcements a
            LEFT JOIN users u ON u.id = a.sender_id
            ORDER BY a.created_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = [
                "id" => (int) $row['id'],
                "title" => $row['title'],
                "message" => $row['message'],
                "createdAt" => $row['created_at'],
                "author" => $row['author'] ?: "Admin",
            ];
        }

        return $list;
    }

    public function update(int $id, string $title, string $message): bool
    {
        $stmt = $this->conn->prepare("UPDATE announcements SET title=?, message=? WHERE id=?");
        $stmt->bind_param("ssi", $title, $message, $id);

        return $stmt->execute();
    }

    public function insert(int $authorId, string $title, string $message)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO announcements (sender_id, group_id, is_broadcast, title, message)
            VALUES (?, NULL, 1, ?, ?)
        ");
        $stmt->bind_param("iss", $authorId, $title, $message);

        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->insert_id;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM announcements WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }
}
