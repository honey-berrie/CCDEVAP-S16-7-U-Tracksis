<?php

namespace App\Models\Admin;

class RecordModel
{
    private \mysqli $conn;

    public function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getTeamDetail(int $teamId): ?array
    {
        $teamStmt = $this->conn->prepare("
            SELECT t.id, t.group_name, t.thesis_title, t.abstract, t.academic_year,
                   t.defense_date, t.status, t.archived_at,
                   CONCAT(u.firstname, ' ', u.lastname) adviser_name, u.email adviser_email
            FROM teams t
            LEFT JOIN users u ON u.id = t.adviser_id
            WHERE t.id = ?
        ");
        $teamStmt->bind_param("i", $teamId);
        $teamStmt->execute();
        $team = $teamStmt->get_result()->fetch_assoc();

        if (!$team) {
            return null;
        }

        $membersStmt = $this->conn->prepare("
            SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) name, gm.member_role
            FROM group_members gm
            JOIN users u ON u.id = gm.user_id
            WHERE gm.group_id = ?
            ORDER BY gm.member_role DESC, u.firstname
        ");
        $membersStmt->bind_param("i", $teamId);
        $membersStmt->execute();
        $members = [];
        $mr = $membersStmt->get_result();
        while ($row = $mr->fetch_assoc()) {
            $members[] = ["id" => (int) $row['id'], "name" => $row['name'], "role" => $row['member_role']];
        }

        $milestoneStmt = $this->conn->prepare("
            SELECT id, name, description, due_date, progress, status, display_order, completed_at
            FROM milestones
            WHERE group_id = ?
            ORDER BY display_order ASC, due_date ASC
        ");
        $milestoneStmt->bind_param("i", $teamId);
        $milestoneStmt->execute();
        $milestones = [];
        $mr = $milestoneStmt->get_result();
        while ($row = $mr->fetch_assoc()) {
            $milestones[] = [
                "id" => (int) $row['id'],
                "name" => $row['name'],
                "description" => $row['description'],
                "dueDate" => $row['due_date'],
                "progress" => (int) $row['progress'],
                "status" => $row['status'],
                "completedAt" => $row['completed_at'],
            ];
        }

        $subStmt = $this->conn->prepare("
            SELECT s.id, s.document_type, s.title, s.file_name, s.status, s.uploaded_at,
                   CONCAT(u.firstname, ' ', u.lastname) uploaded_by, s.review_notes
            FROM submissions s
            LEFT JOIN users u ON u.id = s.uploaded_by
            WHERE s.group_id = ?
            ORDER BY s.uploaded_at DESC
        ");
        $subStmt->bind_param("i", $teamId);
        $subStmt->execute();
        $submissions = [];
        $mr = $subStmt->get_result();
        while ($row = $mr->fetch_assoc()) {
            $submissions[] = [
                "id" => (int) $row['id'],
                "documentType" => $row['document_type'],
                "title" => $row['title'],
                "fileName" => $row['file_name'],
                "status" => $row['status'],
                "uploadedAt" => $row['uploaded_at'],
                "uploadedBy" => $row['uploaded_by'],
                "reviewNotes" => $row['review_notes'],
            ];
        }

        return [
            "id" => (int) $team['id'],
            "groupName" => $team['group_name'],
            "thesisTitle" => $team['thesis_title'],
            "abstract" => $team['abstract'],
            "academicYear" => $team['academic_year'],
            "defenseDate" => $team['defense_date'],
            "status" => $team['status'],
            "adviser" => $team['adviser_name'] ?: "Unassigned",
            "adviserEmail" => $team['adviser_email'],
            "members" => $members,
            "milestones" => $milestones,
            "submissions" => $submissions,
        ];
    }

    public function getFiltered(string $search, int $adviser): array
    {
        $where = ["t.status = 'active'"];
        $params = [];
        $types = "";

        if ($search !== "") {
            $where[] = "(t.group_name LIKE ? OR t.thesis_title LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like; $params[] = $like;
            $types .= "ss";
        }
        if ($adviser > 0) {
            $where[] = "t.adviser_id = ?";
            $params[] = $adviser;
            $types .= "i";
        }

        $whereSql = "WHERE " . implode(" AND ", $where);

        $baseSql = "
            SELECT
                t.id, t.group_name, t.thesis_title, t.defense_date, t.academic_year,
                CONCAT(u.firstname, ' ', u.lastname) adviser_name,
                ROUND(AVG(m.progress)) avg_progress,
                SUM(m.due_date IS NOT NULL AND m.due_date < CURDATE() AND m.status <> 'approved') overdue_count,
                SUM(m.status <> 'approved') incomplete_count,
                COUNT(m.id) milestone_count
            FROM teams t
            LEFT JOIN users u ON u.id = t.adviser_id
            LEFT JOIN milestones m ON m.group_id = t.id
            $whereSql
            GROUP BY t.id
        ";

        $listStmt = $this->conn->prepare($baseSql);
        if ($types !== "") {
            $listStmt->bind_param($types, ...$params);
        }
        $listStmt->execute();
        $all = $listStmt->get_result();
        $records = [];
        while ($row = $all->fetch_assoc()) {
            $derivedStatus = "on-track";
            if ((int) $row['milestone_count'] > 0 && (int) $row['incomplete_count'] === 0) {
                $derivedStatus = "completed";
            } elseif ((int) $row['overdue_count'] > 0) {
                $derivedStatus = "behind";
            }

            $records[] = [
                "id" => (int) $row['id'],
                "groupName" => $row['group_name'],
                "thesisTitle" => $row['thesis_title'],
                "adviser" => $row['adviser_name'] ?: "Unassigned",
                "progress" => (int) $row['avg_progress'],
                "status" => $derivedStatus,
                "defenseDate" => $row['defense_date'],
                "academicYear" => $row['academic_year'],
            ];
        }

        return $records;
    }

    public function getAdviserList(): array
    {
        $adviserList = [];
        $advResult = $this->conn->query("SELECT id, CONCAT(firstname,' ',lastname) name FROM users WHERE role='adviser' AND is_active=1 ORDER BY firstname");
        while ($row = $advResult->fetch_assoc()) {
            $adviserList[] = ["id" => (int) $row['id'], "name" => $row['name']];
        }
        return $adviserList;
    }
}
