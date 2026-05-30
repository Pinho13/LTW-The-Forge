<?php
declare(strict_types=1);

class AdminLog
{
    public static function write(PDO $db, int $adminId, string $type, string $description): void
    {
        $db->prepare(
            "INSERT INTO admin_log (admin_id, action_type, description) VALUES (:admin_id, :type, :desc)"
        )->execute([':admin_id' => $adminId, ':type' => $type, ':desc' => $description]);
    }

    public static function getAll(PDO $db): array
    {
        $stmt = $db->query(
            "SELECT l.id, l.action_type, l.description, l.created_at, u.name AS admin_name
             FROM admin_log l
             JOIN user u ON u.user_id = l.admin_id
             ORDER BY l.created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRecent(PDO $db, int $limit = 20): array
    {
        $stmt = $db->prepare(
            "SELECT l.id, l.action_type, l.description, l.created_at, u.name AS admin_name
             FROM admin_log l
             JOIN user u ON u.user_id = l.admin_id
             ORDER BY l.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getStats(PDO $db): array
    {
        $now   = date('Y-m-d');
        $month = date('Y-m-01');

        $q = fn(string $sql) => $db->query($sql)->fetchColumn();

        return [
            'active_members'      => (int)$q("SELECT COUNT(*) FROM user WHERE role='member' AND is_active=1"),
            'new_members_month'   => (int)$q("SELECT COUNT(*) FROM user WHERE role='member' AND is_active=1 AND created_at >= '$month'"),
            'active_trainers'     => (int)$q("SELECT COUNT(*) FROM user WHERE role='trainer' AND is_active=1"),
            'total_trainers'      => (int)$q("SELECT COUNT(*) FROM user WHERE role='trainer'"),
            'sessions_this_week'  => (int)$q("SELECT COUNT(*) FROM class_session WHERE datetime >= date('now','weekday 0','-7 days') AND datetime < date('now','weekday 0')"),
            'sessions_draft'      => (int)$q("SELECT COUNT(*) FROM class WHERE trainer_id IS NULL"),
            'equipment_ready'     => (int)$q("SELECT COUNT(*) FROM equipment_unit WHERE status='available'"),
            'equipment_total'     => (int)$q("SELECT COUNT(*) FROM equipment_unit"),
            'equipment_maintenance' => (int)$q("SELECT COUNT(*) FROM equipment_unit WHERE status='maintenance'"),
            'classes_no_trainer'  => (int)$q("SELECT COUNT(*) FROM class WHERE trainer_id IS NULL"),
            'classes_at_capacity' => (int)$q("SELECT COUNT(*) FROM class_session cs WHERE (SELECT COUNT(*) FROM enrollment e WHERE e.session_id=cs.id AND e.status='enrolled') >= cs.capacity AND cs.datetime >= '$now'"),
        ];
    }

    public static function getAttentionItems(PDO $db): array
    {
        $items = [];
        $now = date('Y-m-d');

        // Classes without a trainer — count sessions this week only (matches what the calendar highlights)
        $weekEnd = date('Y-m-d', strtotime('Sunday this week'));
        $noTrainerSessions = (int)$db->query(
            "SELECT COUNT(*) FROM class_session cs
             JOIN class c ON c.id = cs.class_id
             WHERE c.trainer_id IS NULL AND cs.datetime >= '$now' AND cs.datetime <= '$weekEnd 23:59:59'"
        )->fetchColumn();
        $noTrainerNames = $db->query(
            "SELECT DISTINCT c.name FROM class c
             JOIN class_session cs ON cs.class_id = c.id
             WHERE c.trainer_id IS NULL AND cs.datetime >= '$now' AND cs.datetime <= '$weekEnd 23:59:59'
             LIMIT 5"
        )->fetchAll(PDO::FETCH_COLUMN);
        if ($noTrainerSessions > 0) {
            $items[] = [
                'icon'    => '!',
                'color'   => 'gold',
                'title'   => $noTrainerSessions . ' ' . ($noTrainerSessions === 1 ? 'session' : 'sessions') . ' without a trainer this week',
                'detail'  => implode(' · ', $noTrainerNames),
                'action'  => 'Review',
                'href'    => '/src/pages/admin-classes.php?filter=no_trainer',
            ];
        }

        // Equipment in maintenance
        $maint = (int)$db->query("SELECT COUNT(*) FROM equipment_unit WHERE status='maintenance'")->fetchColumn();
        if ($maint > 0) {
            $items[] = [
                'icon'   => '▲',
                'color'  => 'gold',
                'title'  => $maint . ' ' . ($maint === 1 ? 'piece' : 'pieces') . ' of kit in maintenance',
                'detail' => 'Check service notes on the equipment page.',
                'action' => 'Review',
                'href'   => '/src/pages/equipment-map.php',
            ];
        }

        // Sessions at capacity
        $cap = (int)$db->query(
            "SELECT COUNT(*) FROM class_session cs
             WHERE cs.datetime >= '$now'
             AND (SELECT COUNT(*) FROM enrollment e WHERE e.session_id=cs.id AND e.status='enrolled') >= cs.capacity"
        )->fetchColumn();
        if ($cap > 0) {
            $items[] = [
                'icon'   => '★',
                'color'  => 'gold',
                'title'  => $cap . ' ' . ($cap === 1 ? 'session' : 'sessions') . ' at capacity',
                'detail' => 'Consider opening a second session or extending capacity.',
                'action' => 'Review',
                'href'   => '/src/pages/admin-classes.php?filter=at_capacity',
            ];
        }

        // Banned members
        $banned = (int)$db->query("SELECT COUNT(*) FROM user WHERE role='member' AND is_active=0")->fetchColumn();
        if ($banned > 0) {
            $names = $db->query("SELECT name FROM user WHERE role='member' AND is_active=0 LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
            $items[] = [
                'icon'   => '✕',
                'color'  => 'red',
                'title'  => $banned . ' banned ' . ($banned === 1 ? 'member' : 'members'),
                'detail' => implode(' · ', $names) . ($banned > 3 ? ' + ' . ($banned - 3) . ' more' : ''),
                'action' => 'Review',
                'href'   => '/src/pages/admin-users.php?role=member&status=banned',
            ];
        }

        // Expired subscriptions still marked active
        $expired = (int)$db->query(
            "SELECT COUNT(*) FROM member_subscription WHERE status='active' AND end_date < '$now'"
        )->fetchColumn();
        if ($expired > 0) {
            $items[] = [
                'icon'   => '⚠',
                'color'  => 'gold',
                'title'  => $expired . ' subscription' . ($expired === 1 ? '' : 's') . ' expired but still active',
                'detail' => 'These memberships passed their end date without being updated.',
                'action' => 'Review',
                'href'   => '/src/pages/admin-users.php?role=member&status=all&subscription=expired',
            ];
        }

        // Members on waitlist
        $waitlisted = (int)$db->query("SELECT COUNT(*) FROM enrollment WHERE status='waitlisted'")->fetchColumn();
        if ($waitlisted > 0) {
            $items[] = [
                'icon'   => '↑',
                'color'  => 'gold',
                'title'  => $waitlisted . ' ' . ($waitlisted === 1 ? 'member' : 'members') . ' on waitlist',
                'detail' => 'Spots may have opened — check for promotions.',
                'action' => 'Review',
                'href'   => '/src/pages/admin-classes.php?filter=waitlisted',
            ];
        }

        // Upcoming sessions with no enrollments
        $empty = (int)$db->query(
            "SELECT COUNT(*) FROM class_session cs
             WHERE cs.datetime >= '$now'
             AND (SELECT COUNT(*) FROM enrollment e WHERE e.session_id=cs.id AND e.status='enrolled') = 0"
        )->fetchColumn();
        if ($empty > 0) {
            $items[] = [
                'icon'   => '○',
                'color'  => 'gold',
                'title'  => $empty . ' upcoming ' . ($empty === 1 ? 'session' : 'sessions') . ' with no enrolments',
                'detail' => 'Consider promoting these classes or cancelling.',
                'action' => 'Review',
                'href'   => '/src/pages/admin-classes.php?filter=empty',
            ];
        }

        return $items;
    }
}
