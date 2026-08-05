<?php

namespace App\Models\Admin;

class AnalyticsModel
{
    private \mysqli $conn;

    public function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }

    private function buildSubWhere(int $groupId, int $adviserId, string $status): array
    {
        $where = ["1=1"];
        $params = [];
        $types = "";

        if ($groupId > 0) {
            $where[] = "s.group_id = ?";
            $params[] = $groupId;
            $types .= "i";
        }
        if ($adviserId > 0) {
            $where[] = "t.adviser_id = ?";
            $params[] = $adviserId;
            $types .= "i";
        }
        if ($status !== "") {
            $where[] = "s.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        return [implode(" AND ", $where), $params, $types];
    }

    public function getSummary(int $groupId, int $adviserId, string $status): array
    {
        [$subWhereSql, $subParams, $subTypes] = $this->buildSubWhere($groupId, $adviserId, $status);

        $totalSql = "SELECT COUNT(*) c FROM submissions s JOIN teams t ON t.id = s.group_id WHERE $subWhereSql";
        $onTimeSql = "SELECT COUNT(*) c FROM submissions s JOIN teams t ON t.id = s.group_id WHERE $subWhereSql AND s.status = 'approved'";

        $stmt = $this->conn->prepare($totalSql);
        if ($subTypes !== "") $stmt->bind_param($subTypes, ...$subParams);
        $stmt->execute();
        $totalSubs = (int) $stmt->get_result()->fetch_assoc()['c'];

        $stmt = $this->conn->prepare($onTimeSql);
        if ($subTypes !== "") $stmt->bind_param($subTypes, ...$subParams);
        $stmt->execute();
        $approvedSubs = (int) $stmt->get_result()->fetch_assoc()['c'];

        $onTimeRate = $totalSubs > 0 ? round(($approvedSubs / $totalSubs) * 100) : 0;

        $activeGroups = (int) $this->conn->query("SELECT COUNT(*) c FROM teams WHERE status='active'")->fetch_assoc()['c'];
        $archivedTheses = (int) $this->conn->query("SELECT COUNT(*) c FROM teams WHERE status='archived'")->fetch_assoc()['c'];
        $avgProgress = (int) ($this->conn->query("SELECT ROUND(AVG(progress)) c FROM milestones")->fetch_assoc()['c'] ?? 0);

        return [
            "onTimeRate" => $onTimeRate,
            "activeGroups" => $activeGroups,
            "archivedTheses" => $archivedTheses,
            "avgProgress" => $avgProgress,
        ];
    }

    public function getStatusBreakdown(int $groupId, int $adviserId, string $status): array
    {
        [$subWhereSql, $subParams, $subTypes] = $this->buildSubWhere($groupId, $adviserId, $status);

        $statusSql = "
            SELECT s.status, COUNT(*) total
            FROM submissions s
            JOIN teams t ON t.id = s.group_id
            WHERE $subWhereSql
            GROUP BY s.status
        ";
        $stmt = $this->conn->prepare($statusSql);
        if ($subTypes !== "") $stmt->bind_param($subTypes, ...$subParams);
        $stmt->execute();
        $result = $stmt->get_result();
        $statusBreakdown = ["in-review" => 0, "approved" => 0, "rejected" => 0, "revision-requested" => 0];
        while ($row = $result->fetch_assoc()) {
            $statusBreakdown[$row['status']] = (int) $row['total'];
        }

        return ["labels" => array_keys($statusBreakdown), "values" => array_values($statusBreakdown)];
    }

    public function getSubmissionsOverTime(int $groupId, int $adviserId, string $status): array
    {
        [$subWhereSql, $subParams, $subTypes] = $this->buildSubWhere($groupId, $adviserId, $status);

        $overTimeSql = "
            SELECT MONTH(s.uploaded_at) month, COUNT(*) total
            FROM submissions s
            JOIN teams t ON t.id = s.group_id
            WHERE $subWhereSql
            GROUP BY MONTH(s.uploaded_at)
            ORDER BY MONTH(s.uploaded_at)
        ";
        $stmt = $this->conn->prepare($overTimeSql);
        if ($subTypes !== "") $stmt->bind_param($subTypes, ...$subParams);
        $stmt->execute();
        $result = $stmt->get_result();
        $labels = [];
        $values = [];
        while ($row = $result->fetch_assoc()) {
            $labels[] = date("M", mktime(0, 0, 0, (int) $row['month'], 1));
            $values[] = (int) $row['total'];
        }

        return ["labels" => $labels, "values" => $values];
    }

    public function getGroupProgress(int $groupId, int $adviserId): array
    {
        $groupWhere = ["t.status = 'active'"];
        $groupParams = [];
        $groupTypes = "";
        if ($adviserId > 0) {
            $groupWhere[] = "t.adviser_id = ?";
            $groupParams[] = $adviserId;
            $groupTypes .= "i";
        }
        if ($groupId > 0) {
            $groupWhere[] = "t.id = ?";
            $groupParams[] = $groupId;
            $groupTypes .= "i";
        }
        $groupWhereSql = implode(" AND ", $groupWhere);

        $progressSql = "
            SELECT t.group_name, ROUND(AVG(m.progress)) avg_progress
            FROM teams t
            LEFT JOIN milestones m ON m.group_id = t.id
            WHERE $groupWhereSql
            GROUP BY t.id
            ORDER BY avg_progress DESC
            LIMIT 10
        ";
        $stmt = $this->conn->prepare($progressSql);
        if ($groupTypes !== "") $stmt->bind_param($groupTypes, ...$groupParams);
        $stmt->execute();
        $result = $stmt->get_result();
        $progLabels = [];
        $progValues = [];
        while ($row = $result->fetch_assoc()) {
            $progLabels[] = $row['group_name'];
            $progValues[] = (int) ($row['avg_progress'] ?? 0);
        }

        return ["labels" => $progLabels, "values" => $progValues];
    }

    public function getFilterGroups(): array
    {
        $groups = [];
        $gr = $this->conn->query("SELECT id, group_name FROM teams WHERE status='active' ORDER BY group_name");
        while ($row = $gr->fetch_assoc()) {
            $groups[] = ["id" => (int) $row['id'], "name" => $row['group_name']];
        }
        return $groups;
    }

    public function getFilterAdvisers(): array
    {
        $advisers = [];
        $ar = $this->conn->query("SELECT id, CONCAT(firstname,' ',lastname) name FROM users WHERE role='adviser' AND is_active=1 ORDER BY firstname");
        while ($row = $ar->fetch_assoc()) {
            $advisers[] = ["id" => (int) $row['id'], "name" => $row['name']];
        }
        return $advisers;
    }
}
