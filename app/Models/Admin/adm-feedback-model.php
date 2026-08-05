<?php

namespace App\Models\Admin;

class FeedbackModel
{
    private \mysqli $conn;

    private array $sortColumns = [
        "created_at" => "f.created_at",
        "author_name" => "u.firstname",
        "group_name" => "t.group_name",
    ];

    public function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getFiltered(
        string $search,
        string $sort,
        string $dir,
        int $perPage,
        int $offset
    ): array {
        $where = [];
        $params = [];
        $types = "";

        if ($search !== "") {
            $where[] = "(CONCAT(u.firstname, ' ', u.lastname) LIKE ? OR u.email LIKE ? OR t.group_name LIKE ? OR t.thesis_title LIKE ? OR f.message LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
            $types .= "sssss";
        }

        $whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        $sortColumn = $this->sortColumns[$sort] ?? $this->sortColumns["created_at"];
        $sortDir = strtoupper($dir) === "ASC" ? "ASC" : "DESC";

        $countSql = "
            SELECT COUNT(*) c
            FROM feedback f
            JOIN users u ON u.id = f.given_by
            JOIN teams t ON t.id = f.group_id
            $whereSql
        ";
        $countStmt = $this->conn->prepare($countSql);
        if ($types !== "") {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) $countStmt->get_result()->fetch_assoc()['c'];

        $listSql = "
            SELECT
                f.id, f.message, f.created_at, f.author_role,
                t.group_name, t.thesis_title,
                CONCAT(u.firstname, ' ', u.lastname) AS author_name,
                u.email AS author_email,
                s.title AS submission_title,
                s.document_type
            FROM feedback f
            JOIN users u ON u.id = f.given_by
            JOIN teams t ON t.id = f.group_id
            LEFT JOIN submissions s ON s.id = f.submission_id
            $whereSql
            ORDER BY $sortColumn $sortDir
            LIMIT ? OFFSET ?
        ";
        $listStmt = $this->conn->prepare($listSql);
        $listTypes = $types . "ii";
        $listParams = array_merge($params, [$perPage, $offset]);
        $listStmt->bind_param($listTypes, ...$listParams);
        $listStmt->execute();
        $result = $listStmt->get_result();

        $feedback = [];
        while ($row = $result->fetch_assoc()) {
            $feedback[] = [
                "id" => (int) $row['id'],
                "authorName" => $row['author_name'],
                "authorEmail" => $row['author_email'],
                "authorRole" => $row['author_role'],
                "groupName" => $row['group_name'],
                "thesisTitle" => $row['thesis_title'],
                "message" => $row['message'],
                "submissionTitle" => $row['submission_title'],
                "documentType" => $row['document_type'],
                "createdAt" => $row['created_at'],
            ];
        }

        return ["feedback" => $feedback, "total" => $total];
    }
}
