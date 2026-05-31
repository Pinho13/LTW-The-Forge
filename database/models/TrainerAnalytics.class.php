<?php
declare(strict_types=1);

class TrainerAnalytics {

    public static function getStats(PDO $db, int $trainerId): array {
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM class_session cs
            JOIN class c ON c.id = cs.class_id
            WHERE c.trainer_id = :tid
              AND cs.datetime >= date('now', 'weekday 1', '-7 days')
              AND cs.datetime <  date('now', 'weekday 1')
        ");
        $stmt->execute([':tid' => $trainerId]);
        $sessionsThisWeek = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT COUNT(*) FROM class_session cs
            JOIN class c ON c.id = cs.class_id
            WHERE c.trainer_id = :tid AND cs.datetime >= datetime('now')
        ");
        $stmt->execute([':tid' => $trainerId]);
        $upcomingSessions = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT e.member_id)
            FROM enrollment e
            JOIN class_session cs ON cs.id = e.session_id
            JOIN class c ON c.id = cs.class_id
            WHERE c.trainer_id = :tid AND e.status IN ('enrolled','completed')
        ");
        $stmt->execute([':tid' => $trainerId]);
        $totalStudents = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT ROUND(AVG(r.rating), 1)
            FROM review r
            JOIN class c ON c.id = r.class_id
            WHERE c.trainer_id = :tid
        ");
        $stmt->execute([':tid' => $trainerId]);
        $avgRating = $stmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT c.id) FROM class c WHERE c.trainer_id = :tid
        ");
        $stmt->execute([':tid' => $trainerId]);
        $totalClasses = (int)$stmt->fetchColumn();

        return compact('sessionsThisWeek', 'upcomingSessions', 'totalStudents', 'avgRating', 'totalClasses');
    }

    public static function getEnrollmentTrend(PDO $db, int $trainerId, int $months = 6): array {
        $stmt = $db->prepare("
            SELECT strftime('%Y-%m', cs.datetime) AS month,
                   COUNT(*) AS enrollments
            FROM enrollment e
            JOIN class_session cs ON cs.id = e.session_id
            JOIN class c ON c.id = cs.class_id
            WHERE c.trainer_id = :tid
              AND e.status IN ('enrolled','completed','missed')
              AND cs.datetime >= date('now', '-' || :months || ' months')
            GROUP BY strftime('%Y-%m', cs.datetime)
            ORDER BY month ASC
        ");
        $stmt->execute([':tid' => $trainerId, ':months' => $months]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getClassesByEnrollment(PDO $db, int $trainerId, int $limit = 8): array {
        $stmt = $db->prepare("
            SELECT c.name,
                   COUNT(CASE WHEN e.status IN ('enrolled','completed') THEN 1 END) AS total_enrolled,
                   ROUND(AVG(r.rating), 1) AS avg_rating
            FROM class c
            LEFT JOIN class_session cs ON cs.class_id = c.id
            LEFT JOIN enrollment e ON e.session_id = cs.id
            LEFT JOIN review r ON r.class_id = c.id
            WHERE c.trainer_id = :tid
            GROUP BY c.id
            ORDER BY total_enrolled DESC
            LIMIT :limit
        ");
        $stmt->execute([':tid' => $trainerId, ':limit' => $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSessionsByDayOfWeek(PDO $db, int $trainerId): array {
        $stmt = $db->prepare("
            SELECT strftime('%w', cs.datetime) AS dow, COUNT(*) AS sessions
            FROM class_session cs
            JOIN class c ON c.id = cs.class_id
            WHERE c.trainer_id = :tid
            GROUP BY dow ORDER BY dow ASC
        ");
        $stmt->execute([':tid' => $trainerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = array_fill(0, 7, 0);
        foreach ($rows as $r) $map[(int)$r['dow']] = (int)$r['sessions'];
        return $map;
    }

    public static function getUpcomingSessions(PDO $db, int $trainerId, int $limit = 5): array {
        $stmt = $db->prepare("
            SELECT c.name,
                   cs.datetime,
                   cs.room,
                   COUNT(CASE WHEN e.status = 'enrolled' THEN 1 END) AS enrolled,
                   cs.capacity
            FROM class_session cs
            JOIN class c ON c.id = cs.class_id
            LEFT JOIN enrollment e ON e.session_id = cs.id
            WHERE c.trainer_id = :tid AND cs.datetime >= datetime('now')
            GROUP BY cs.id
            ORDER BY cs.datetime ASC
            LIMIT :limit
        ");
        $stmt->execute([':tid' => $trainerId, ':limit' => $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
