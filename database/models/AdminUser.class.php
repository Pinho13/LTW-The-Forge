<?php
declare(strict_types=1);

class AdminUser
{
    public static function getAll(PDO $db, string $role = '', string $search = '', string $status = 'active', int $excludeId = 0, string $joined = '', string $subscription = ''): array
    {
        $where = [];
        $params = [];

        if ($excludeId > 0) {
            $where[] = 'u.user_id != :exclude';
            $params[':exclude'] = $excludeId;
        }
        if ($role !== '') {
            $where[] = 'u.role = :role';
            $params[':role'] = $role;
        }
        if ($search !== '') {
            $localPart = '%' . explode('@', $search)[0] . '%';
            $where[] = '(u.name LIKE :search OR u.username LIKE :search OR SUBSTR(u.email, 1, INSTR(u.email, \'@\') - 1) LIKE :email_local)';
            $params[':search']      = '%' . $search . '%';
            $params[':email_local'] = $localPart;
        }
        if ($status === 'active') {
            $where[] = 'u.is_active = 1 AND (ms.status = \'active\' OR ms.status IS NULL)';
        } elseif ($status === 'all') {
            // no filter
        } elseif ($status === 'banned') {
            $where[] = 'u.is_active = 0';
        } elseif ($status === 'frozen') {
            $where[] = 'u.is_active = 1 AND ms.status = \'frozen\'';
        }
        if ($joined === 'week') {
            $where[] = "u.created_at >= datetime('now', '+1 hour', '-7 days')";
        } elseif ($joined === 'month') {
            $where[] = "u.created_at >= datetime('now', '+1 hour', 'start of month')";
        } elseif ($joined === 'year') {
            $where[] = "u.created_at >= datetime('now', '+1 hour', 'start of year')";
        }
        if ($subscription === 'expired') {
            $where[] = "u.user_id IN (SELECT member_id FROM member_subscription WHERE status='active' AND end_date < date('now'))";
        }

        $sql = "SELECT u.user_id, u.name, u.username, u.email, u.phone, u.role,
                       u.is_active, u.created_at,
                       tp.bio, tp.specializations, tp.certifications, tp.is_featured AS trainer_featured,
                       mp.name AS plan_name, ms.status AS sub_status, ms.end_date AS sub_end_date
                FROM user u
                LEFT JOIN trainer_profile tp ON tp.user_id = u.user_id
                LEFT JOIN member_subscription ms ON ms.id = (
                    SELECT id FROM member_subscription
                    WHERE member_id = u.user_id AND status IN ('active', 'frozen')
                    ORDER BY end_date DESC LIMIT 1
                )
                LEFT JOIN membership_plan mp ON mp.id = ms.plan_id"
             . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '')
             . " ORDER BY u.role ASC, u.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById(PDO $db, int $userId): ?array
    {
        $stmt = $db->prepare(
            "SELECT u.user_id, u.name, u.username, u.email, u.phone, u.role,
                    u.is_active, u.created_at,
                    tp.bio, tp.specializations, tp.certifications
             FROM user u
             LEFT JOIN trainer_profile tp ON tp.user_id = u.user_id
             WHERE u.user_id = :id"
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateDetails(PDO $db, int $userId, string $name, string $email, string $phone): void
    {
        $stmt = $db->prepare(
            "UPDATE user SET name = :name, email = :email, phone = :phone WHERE user_id = :id"
        );
        $stmt->execute([':name' => $name, ':email' => $email, ':phone' => $phone, ':id' => $userId]);
    }

    public static function setRole(PDO $db, int $userId, string $role): void
    {
        $stmt = $db->prepare("UPDATE user SET role = :role WHERE user_id = :id");
        $stmt->execute([':role' => $role, ':id' => $userId]);
    }

    public static function setActive(PDO $db, int $userId, bool $active): void
    {
        $stmt = $db->prepare("UPDATE user SET is_active = :v WHERE user_id = :id");
        $stmt->execute([':v' => $active ? 1 : 0, ':id' => $userId]);
    }

    public static function delete(PDO $db, int $userId): void
    {
        $stmt = $db->prepare("DELETE FROM user WHERE user_id = :id");
        $stmt->execute([':id' => $userId]);
    }

    public static function emailExists(PDO $db, string $email, int $excludeId): bool
    {
        $stmt = $db->prepare("SELECT COUNT(*) FROM user WHERE email = :email AND user_id != :id");
        $stmt->execute([':email' => $email, ':id' => $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function usernameExists(PDO $db, string $username, int $excludeId): bool
    {
        $stmt = $db->prepare("SELECT COUNT(*) FROM user WHERE username = :u AND user_id != :id");
        $stmt->execute([':u' => $username, ':id' => $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
