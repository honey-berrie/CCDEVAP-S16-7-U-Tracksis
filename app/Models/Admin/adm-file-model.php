<?php

namespace App\Models\Admin;

class FileModel
{
    private \mysqli $conn;

    private array $sortColumns = [
        "uploaded_at" => "s.uploaded_at",
        "file_name" => "s.file_name",
        "group_name" => "t.group_name",
        "document_type" => "s.document_type",
    ];

    public function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getFiltered(
        string $search,
        string $documentType,
        string $sort,
        string $dir,
        int $perPage,
        int $offset
    ): array {
        $where = [];
        $params = [];
        $types = "";

        if ($search !== "") {
            $where[] = "(s.file_name LIKE ? OR t.group_name LIKE ? OR t.thesis_title LIKE ? OR CONCAT(uu.firstname, ' ', uu.lastname) LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
            $types .= "ssss";
        }

        if ($documentType !== "") {
            $where[] = "s.document_type = ?";
            $params[] = $documentType;
            $types .= "s";
        }

        $whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        $sortColumn = $this->sortColumns[$sort] ?? $this->sortColumns["uploaded_at"];
        $sortDir = strtoupper($dir) === "ASC" ? "ASC" : "DESC";

        $countSql = "
            SELECT COUNT(*) c
            FROM submissions s
            JOIN teams t ON t.id = s.group_id
            LEFT JOIN users uu ON uu.id = s.uploaded_by
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
                s.id, s.file_name, s.file_path, s.file_size, s.mime_type,
                s.document_type, s.uploaded_at,
                t.group_name, t.thesis_title,
                m.name AS milestone_name,
                CONCAT(uu.firstname, ' ', uu.lastname) AS uploaded_by,
                CONCAT(av.firstname, ' ', av.lastname) AS adviser_name
            FROM submissions s
            JOIN teams t ON t.id = s.group_id
            LEFT JOIN milestones m ON m.id = s.milestone_id
            LEFT JOIN users uu ON uu.id = s.uploaded_by
            LEFT JOIN users av ON av.id = t.adviser_id
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

        $files = [];
        while ($row = $result->fetch_assoc()) {
            $files[] = [
                "id" => (int) $row['id'],
                "fileName" => $row['file_name'],
                "filePath" => $row['file_path'],
                "fileSize" => (int) $row['file_size'],
                "mimeType" => $row['mime_type'],
                "documentType" => $row['document_type'],
                "uploadedAt" => $row['uploaded_at'],
                "groupName" => $row['group_name'],
                "thesisTitle" => $row['thesis_title'],
                "milestone" => $row['milestone_name'],
                "uploadedBy" => $row['uploaded_by'],
                "adviser" => $row['adviser_name'],
            ];
        }

        return ["files" => $files, "total" => $total];
    }

    public function getDocumentTypeCounts(): array
    {
        $counts = [];
        $result = $this->conn->query("SELECT document_type, COUNT(*) c FROM submissions GROUP BY document_type");
        while ($row = $result->fetch_assoc()) {
            $counts[$row['document_type']] = (int) $row['c'];
        }
        return $counts;
    }
}
