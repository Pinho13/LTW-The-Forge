<?php
declare(strict_types=1);

class AdminAnalytics {

    public static function getTopClasses(PDO $db, int $limit = 8): array {
        $stmt = $db->prepare("
            SELECT c.name,
                   ct.name AS type,
                   COUNT(CASE WHEN e.status IN ('enrolled','completed') THEN 1 END) AS total_enrolled,
                   ROUND(AVG(r.rating), 1) AS avg_rating,
                   COUNT(DISTINCT r.id) AS review_count
            FROM class c
            LEFT JOIN class_type ct ON ct.id = c.type_id
            LEFT JOIN class_session cs ON cs.class_id = c.id
            LEFT JOIN enrollment e ON e.session_id = cs.id
            LEFT JOIN review r ON r.class_id = c.id
            GROUP BY c.id
            ORDER BY total_enrolled DESC
            LIMIT :limit
        ");
        $stmt->execute([':limit' => $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEnrollmentByMonth(PDO $db, int $months = 6): array {
        $stmt = $db->prepare("
            SELECT strftime('%Y-%m', cs.datetime) AS month,
                   COUNT(*) AS enrollments
            FROM enrollment e
            JOIN class_session cs ON cs.id = e.session_id
            WHERE e.status IN ('enrolled','completed','missed')
              AND cs.datetime >= date('now', '-' || :months || ' months')
            GROUP BY strftime('%Y-%m', cs.datetime)
            ORDER BY month ASC
        ");
        $stmt->execute([':months' => $months]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEquipmentUsage(PDO $db): array {
        $stmt = $db->query("
            SELECT e.name AS equipment_name,
                   e.type,
                   COUNT(er.id) AS reservation_count,
                   COUNT(DISTINCT eu.id) AS unit_count,
                   SUM(CASE WHEN eu.status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance_count
            FROM equipment e
            LEFT JOIN equipment_unit eu ON eu.equipment_id = e.id
            LEFT JOIN equipment_reservation er ON er.unit_id = eu.id
            GROUP BY e.id
            ORDER BY reservation_count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getMemberRetention(PDO $db): array {
        $active  = (int)$db->query("SELECT COUNT(*) FROM member_subscription WHERE status = 'active' AND end_date >= date('now')")->fetchColumn();
        $expired = (int)$db->query("SELECT COUNT(*) FROM member_subscription WHERE status = 'active' AND end_date < date('now')")->fetchColumn();
        $frozen  = (int)$db->query("SELECT COUNT(*) FROM member_subscription WHERE status = 'frozen'")->fetchColumn();
        $cancelled = (int)$db->query("SELECT COUNT(*) FROM member_subscription WHERE status IN ('expired','cancelled')")->fetchColumn();
        return compact('active', 'expired', 'frozen', 'cancelled');
    }

    public static function getClassTypeDistribution(PDO $db): array {
        $stmt = $db->query("
            SELECT ct.name AS type,
                   COUNT(DISTINCT c.id) AS class_count,
                   COUNT(CASE WHEN e.status IN ('enrolled','completed') THEN 1 END) AS total_enrolled
            FROM class_type ct
            LEFT JOIN class c ON c.type_id = ct.id
            LEFT JOIN class_session cs ON cs.class_id = c.id
            LEFT JOIN enrollment e ON e.session_id = cs.id
            GROUP BY ct.id
            ORDER BY total_enrolled DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getGymVisitsByDay(PDO $db): array {
        // Derives activity from class enrollments + equipment reservations
        // (gym_visit table is seed-only; real activity lives in these two tables)
        $stmt = $db->query("
            SELECT strftime('%w', activity_dt) AS dow, COUNT(*) AS visits
            FROM (
                SELECT cs.datetime AS activity_dt
                FROM enrollment e
                JOIN class_session cs ON cs.id = e.session_id
                WHERE e.status IN ('completed', 'enrolled')
                  AND cs.datetime <= datetime('now', '+1 hour')
                UNION ALL
                SELECT start_datetime AS activity_dt
                FROM equipment_reservation
                WHERE start_datetime <= datetime('now', '+1 hour')
            )
            GROUP BY dow
            ORDER BY dow ASC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map  = array_fill(0, 7, 0);
        foreach ($rows as $r) $map[(int)$r['dow']] = (int)$r['visits'];
        return $map;
    }

    public static function getNewMembersPerMonth(PDO $db, int $months = 6): array {
        $stmt = $db->prepare("
            SELECT strftime('%Y-%m', created_at) AS month,
                   COUNT(*) AS new_members
            FROM user
            WHERE role = 'member'
              AND created_at >= date('now', '-' || :months || ' months')
            GROUP BY strftime('%Y-%m', created_at)
            ORDER BY month ASC
        ");
        $stmt->execute([':months' => $months]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTopTrainers(PDO $db, int $limit = 5): array {
        $stmt = $db->prepare("
            SELECT u.name,
                   COUNT(DISTINCT cs.id) AS sessions_taught,
                   COUNT(CASE WHEN e.status IN ('enrolled','completed') THEN 1 END) AS total_students,
                   ROUND(AVG(r.rating), 1) AS avg_rating
            FROM user u
            JOIN class c ON c.trainer_id = u.user_id
            LEFT JOIN class_session cs ON cs.class_id = c.id
            LEFT JOIN enrollment e ON e.session_id = cs.id
            LEFT JOIN review r ON r.class_id = c.id
            WHERE u.role = 'trainer'
            GROUP BY u.user_id
            ORDER BY total_students DESC
            LIMIT :limit
        ");
        $stmt->execute([':limit' => $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
