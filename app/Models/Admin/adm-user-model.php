<?php

namespace App\Models\Admin;

class UserModel
{
    private \mysqli $conn;

    public function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getPaginated(string $search, string $role, string $status, int $perPage, int $offset): array
    {
        $allowedRoles = ['student', 'adviser', 'coordinator', 'admin'];

        $where = [];
        $params = [];
        $types = "";

        if ($search !== "") {
            $where[] = "(firstname LIKE ? OR lastname LIKE ? OR email LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like; $params[] = $like; $params[] = $like;
            $types .= "sss";
        }

        if ($role !== "" && in_array($role, $allowedRoles, true)) {
            $where[] = "role = ?";
            $params[] = $role;
            $types .= "s";
        }

        if ($status === "active") {
            $where[] = "is_active = 1";
        } elseif ($status === "inactive") {
            $where[] = "is_active = 0";
        }

        $whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        $countSql = "SELECT COUNT(*) c FROM users $whereSql";
        $countStmt = $this->conn->prepare($countSql);
        if ($types !== "") {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) $countStmt->get_result()->fetch_assoc()['c'];

        $listSql = "
            SELECT id, role, firstname, lastname, email, is_active, last_login_at, created_at
            FROM users
            $whereSql
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ";
        $listStmt = $this->conn->prepare($listSql);
        $listTypes = $types . "ii";
        $listParams = array_merge($params, [$perPage, $offset]);
        $listStmt->bind_param($listTypes, ...$listParams);
        $listStmt->execute();
        $result = $listStmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                "id" => (int) $row['id'],
                "role" => $row['role'],
                "firstname" => $row['firstname'],
                "lastname" => $row['lastname'],
                "email" => $row['email'],
                "isActive" => (bool) $row['is_active'],
                "lastLogin" => $row['last_login_at'],
                "createdAt" => $row['created_at'],
            ];
        }

        return ["users" => $users, "total" => $total];
    }

    public function getRoleCounts(): array
    {
        $roleCounts = ["student" => 0, "adviser" => 0, "coordinator" => 0, "admin" => 0];
        $rcResult = $this->conn->query("SELECT role, COUNT(*) c FROM users GROUP BY role");
        while ($row = $rcResult->fetch_assoc()) {
            $roleCounts[$row['role']] = (int) $row['c'];
        }
        return $roleCounts;
    }

    public function getActiveAdvisersWithLoad(): array
    {
        $advisers = [];
        $adviserResult = $this->conn->query("
            SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) name, COUNT(t.id) AS `load`
            FROM users u
            LEFT JOIN teams t ON t.adviser_id = u.id AND t.status = 'active'
            WHERE u.role = 'adviser' AND u.is_active = 1
            GROUP BY u.id
            ORDER BY u.firstname
        ");
        while ($row = $adviserResult->fetch_assoc()) {
            $advisers[] = [
                "id" => (int) $row['id'],
                "name" => $row['name'],
                "load" => (int) $row['load'],
            ];
        }
        return $advisers;
    }

    public function getUnassignedStudents(): array
    {
        $students = [];
        $studentResult = $this->conn->query("
            SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) name
            FROM users u
            WHERE u.role = 'student' AND u.is_active = 1
              AND u.id NOT IN (SELECT user_id FROM group_members)
            ORDER BY u.firstname
        ");
        while ($row = $studentResult->fetch_assoc()) {
            $students[] = ["id" => (int) $row['id'], "name" => $row['name']];
        }
        return $students;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $check = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
            $check->bind_param("si", $email, $excludeId);
        } else {
            $check = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
        }
        $check->execute();
        return $check->get_result()->num_rows > 0;
    }

    public function createUser(string $role, string $firstname, string $lastname, string $email, string $password)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO users (role, firstname, lastname, email, password_hash)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $role, $firstname, $lastname, $email, $password);

        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->insert_id;
    }

    public function updateUser(int $id, string $firstname, string $lastname, string $email, string $role, int $isActive): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE users SET firstname=?, lastname=?, email=?, role=?, is_active=?
             WHERE id=?"
        );
        $stmt->bind_param("ssssii", $firstname, $lastname, $email, $role, $isActive, $id);

        return $stmt->execute();
    }

    public function deleteUser(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function getDefaultSectionId(): ?int
    {
        $result = $this->conn->query("SELECT id FROM sections ORDER BY id ASC LIMIT 1");
        $row = $result ? $result->fetch_assoc() : null;

        return $row ? (int) $row['id'] : null;
    }

    public function createTeamWithMembers(
        string $groupName,
        string $thesisTitle,
        string $abstract,
        ?int $adviserId,
        string $academicYear,
        ?string $defenseDate,
        array $memberIds
    ) {
        $sectionId = $this->getDefaultSectionId();

        if ($sectionId === null) {
            throw new \Exception("No section is available to assign this thesis group to. Please ask an admin to create a section first.");
        }

        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO teams (group_name, thesis_title, abstract, adviser_id, section_id, academic_year, defense_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssiiss",
                $groupName,
                $thesisTitle,
                $abstract,
                $adviserId,
                $sectionId,
                $academicYear,
                $defenseDate
            );
            $stmt->execute();
            $teamId = $stmt->insert_id;

            if (count($memberIds) > 0) {
                $memberStmt = $this->conn->prepare(
                    "INSERT INTO group_members (group_id, user_id, member_role) VALUES (?, ?, 'Member')"
                );
                foreach ($memberIds as $studentId) {
                    $studentId = (int) $studentId;
                    if ($studentId > 0) {
                        $memberStmt->bind_param("ii", $teamId, $studentId);
                        $memberStmt->execute();
                    }
                }
            }

            $this->conn->commit();
            return $teamId;
        } catch (\Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
