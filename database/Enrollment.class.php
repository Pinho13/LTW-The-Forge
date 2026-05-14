<?php
declare(strict_types=1);

class Enrollment
{
    public static function countEnrolledThisMonth(PDO $db, int $memberId): int
    {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM enrollment
             JOIN class_session ON class_session.id = enrollment.session_id
             WHERE enrollment.member_id = :member_id
               AND enrollment.status = 'enrolled'
               AND strftime('%Y-%m', class_session.datetime) = strftime('%Y-%m', 'now')"
        );
        $stmt->execute([':member_id' => $memberId]);

        return (int) $stmt->fetchColumn();
    }

    public static function countUpcoming(PDO $db, int $memberId): int
    {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM enrollment
             JOIN class_session ON class_session.id = enrollment.session_id
             WHERE enrollment.member_id = :member_id
               AND enrollment.status = 'enrolled'
               AND class_session.datetime > datetime('now')"
        );
        $stmt->execute([':member_id' => $memberId]);

        return (int) $stmt->fetchColumn();
    }

    public static function findNextForMember(PDO $db, int $memberId): ?array
    {
        $stmt = $db->prepare(
            "SELECT class.name AS class_name,
                    class_session.datetime,
                    class_session.room,
                    user.name AS trainer_name
             FROM enrollment
             JOIN class_session ON class_session.id = enrollment.session_id
             JOIN class ON class.id = class_session.class_id
             LEFT JOIN user ON user.user_id = class.trainer_id
             WHERE enrollment.member_id = :member_id
               AND enrollment.status = 'enrolled'
               AND class_session.datetime > datetime('now', 'localtime')
             ORDER BY class_session.datetime ASC
             LIMIT 1"
        );
        $stmt->execute([':member_id' => $memberId]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getUpcomingForMember(PDO $db, int $memberId, int $offset = 0): array
    {
        $stmt = $db->prepare(
            "SELECT enrollment.id,
                    enrollment.status,
                    enrollment.enrolled_at,
                    class.name AS class_name,
                    class.intensity,
                    class.duration_minutes,
                    class_session.datetime,
                    class_session.room,
                    user.name AS trainer_name,
                    CASE WHEN enrollment.status = 'waitlisted' THEN (
                        SELECT COUNT(*) + 1 FROM enrollment e2
                        WHERE e2.session_id = enrollment.session_id
                          AND e2.status = 'waitlisted'
                          AND e2.enrolled_at < enrollment.enrolled_at
                    ) ELSE NULL END AS waitlist_position
             FROM enrollment
             JOIN class_session ON class_session.id = enrollment.session_id
             JOIN class ON class.id = class_session.class_id
             LEFT JOIN user ON user.user_id = class.trainer_id
             WHERE enrollment.member_id = :member_id
               AND enrollment.status IN ('enrolled', 'waitlisted')
               AND class_session.datetime > datetime('now', 'localtime')
             ORDER BY class_session.datetime ASC
             LIMIT 31 OFFSET :offset"
        );
        $stmt->execute([':member_id' => $memberId, ':offset' => $offset]);
        return $stmt->fetchAll();
    }

    public static function getPastForMember(PDO $db, int $memberId, int $offset = 0): array
    {
        $stmt = $db->prepare(
            "SELECT enrollment.id,
                enrollment.status,
                class.name AS class_name,
                class.intensity,
                class.duration_minutes,
                class_session.datetime,
                class_session.room,
                user.name AS trainer_name
            FROM enrollment
            JOIN class_session ON class_session.id = enrollment.session_id
            JOIN class ON class.id = class_session.class_id
            LEFT JOIN user ON user.user_id = class.trainer_id
            WHERE enrollment.member_id = :member_id
            AND class_session.datetime < datetime('now', 'localtime')
            AND enrollment.status NOT IN ('cancelled', 'waitlisted')
            ORDER BY class_session.datetime DESC
            LIMIT 31 OFFSET :offset"
        );
        $stmt->execute([':member_id' => $memberId, ':offset' => $offset]);
        return $stmt->fetchAll();
    }

    public static function cancelForMember(PDO $db, int $enrollmentId, int $memberId): bool
    {
        $stmt = $db->prepare(
            "UPDATE enrollment SET status = 'cancelled'
            WHERE id = :id
            AND member_id = :member_id
            AND status IN ('enrolled', 'waitlisted')"
        );
        $stmt->execute([':id' => $enrollmentId, ':member_id' => $memberId]);
        return $stmt->rowCount() > 0;
    }

    public static function getRecentActivity(PDO $db, int $memberId): array
    {
        $stmt = $db->prepare(
            "SELECT class.name AS class_name,
                    class_session.datetime,
                    enrollment.status
             FROM enrollment
             JOIN class_session ON class_session.id = enrollment.session_id
             JOIN class ON class.id = class_session.class_id
             WHERE enrollment.member_id = :member_id
               AND enrollment.status IN ('completed', 'missed')
               AND class_session.datetime <= datetime('now', 'localtime')
               AND class_session.datetime >= datetime('now', 'localtime', '-14 days')
             ORDER BY class_session.datetime DESC
             LIMIT 7"
        );
        $stmt->execute([':member_id' => $memberId]);
        return $stmt->fetchAll();
    }
}
