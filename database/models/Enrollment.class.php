<?php
declare(strict_types=1);

class Enrollment
{
    public const PAGE_SIZE = 30;

    public static function countEnrolledThisMonth(PDO $db, int $memberId): int
    {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM enrollment
             JOIN class_session ON class_session.id = enrollment.session_id
             WHERE enrollment.member_id = :member_id
               AND enrollment.status = 'enrolled'
               AND strftime('%Y-%m', class_session.datetime) = strftime('%Y-%m', 'now', 'localtime')"
        );
        $stmt->execute([':member_id' => $memberId]);

        return (int) $stmt->fetchColumn();
    }

    public static function countUpcoming(PDO $db, int $memberId): int
    {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM equipment_reservation
             WHERE member_id = :member_id
               AND start_datetime > datetime('now', 'localtime')"
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
                    class_type.name AS type_name,
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
             LEFT JOIN class_type ON class_type.id = class.type_id
             WHERE enrollment.member_id = :member_id
               AND enrollment.status IN ('enrolled', 'waitlisted')
               AND class_session.datetime > datetime('now', 'localtime')
             ORDER BY class_session.datetime ASC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':member_id', $memberId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', self::PAGE_SIZE + 1, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getPastForMember(PDO $db, int $memberId, int $offset = 0): array
    {
        $stmt = $db->prepare(
            "SELECT enrollment.id,
                enrollment.status,
                class.id AS class_id,
                class.name AS class_name,
                class.intensity,
                class.duration_minutes,
                class_session.datetime,
                class_session.room,
                user.name AS trainer_name,
                class_type.name AS type_name,
                CASE WHEN review.id IS NOT NULL THEN 1 ELSE 0 END AS has_review,
                review.rating AS existing_rating,
                review.comment AS existing_comment
            FROM enrollment
            JOIN class_session ON class_session.id = enrollment.session_id
            JOIN class ON class.id = class_session.class_id
            LEFT JOIN user ON user.user_id = class.trainer_id
            LEFT JOIN class_type ON class_type.id = class.type_id
            LEFT JOIN review ON review.class_id = class.id AND review.member_id = enrollment.member_id
            WHERE enrollment.member_id = :member_id
            AND class_session.datetime < datetime('now', 'localtime')
            AND enrollment.status NOT IN ('cancelled', 'waitlisted')
            ORDER BY class_session.datetime DESC
            LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':member_id', $memberId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', self::PAGE_SIZE + 1, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getStaleForMember(PDO $db, int $memberId, string $now): array
    {
        $stmt = $db->prepare(
            "SELECT enrollment.id,
                    class.name AS class_name,
                    class_session.datetime,
                    user.name AS trainer_name
                FROM enrollment
                JOIN class_session ON class_session.id = enrollment.session_id
                JOIN class ON class.id = class_session.class_id
                LEFT JOIN user ON user.user_id = class.trainer_id
                WHERE enrollment.member_id = :member_id
                AND enrollment.status = 'enrolled'
                AND class_session.datetime < :now
                ORDER BY class_session.datetime ASC"
        );
        $stmt->bindValue(':member_id', $memberId, PDO::PARAM_INT);
        $stmt->bindValue(':now', $now);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function updateStatus(PDO $db, int $enrollmentId, int $memberId, string $status, string $now): bool 
    {
        $stmt = $db->prepare(
            "UPDATE enrollment SET status = :status
                WHERE id = :enrollment_id
                AND member_id = :member_id
                AND status = 'enrolled'
                AND EXISTS (
                    SELECT 1 FROM class_session
                    WHERE class_session.id = enrollment.session_id
                        AND class_session.datetime < :now
                )"
        );
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':enrollment_id', $enrollmentId, PDO::PARAM_INT);
        $stmt->bindValue(':member_id', $memberId, PDO::PARAM_INT);
        $stmt->bindValue(':now', $now);
        $stmt->execute();
        return $stmt->rowCount() > 0;
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

    public static function promoteWaitlist(PDO $db, int $cancelledEnrollmentId): void
    {
        $sessionId = $db->prepare(
            "SELECT session_id FROM enrollment WHERE id = :id"
        );
        $sessionId->execute([':id' => $cancelledEnrollmentId]);
        $sid = $sessionId->fetchColumn();
        if (!$sid) return;

        $capacity = $db->prepare(
            "SELECT capacity FROM class_session WHERE id = :id"
        );
        $capacity->execute([':id' => $sid]);
        $cap = (int) $capacity->fetchColumn();

        $enrolled = $db->prepare(
            "SELECT COUNT(*) FROM enrollment WHERE session_id = :sid AND status = 'enrolled'"
        );
        $enrolled->execute([':sid' => $sid]);
        $count = (int) $enrolled->fetchColumn();

        if ($count >= $cap) return;

        $next = $db->prepare(
            "SELECT id FROM enrollment
             WHERE session_id = :sid AND status = 'waitlisted'
             ORDER BY enrolled_at ASC LIMIT 1"
        );
        $next->execute([':sid' => $sid]);
        $nextId = $next->fetchColumn();
        if (!$nextId) return;

        $promote = $db->prepare(
            "UPDATE enrollment SET status = 'enrolled' WHERE id = :id"
        );
        $promote->execute([':id' => $nextId]);
    }

    public static function enroll(PDO $db, int $memberId, int $sessionId): string
    {
        $cap = $db->prepare("SELECT capacity FROM class_session WHERE id = :id");
        $cap->execute([':id' => $sessionId]);
        $capacity = (int) $cap->fetchColumn();

        $enrolled = $db->prepare(
            "SELECT COUNT(*) FROM enrollment WHERE session_id = :sid AND status = 'enrolled'"
        );
        $enrolled->execute([':sid' => $sessionId]);
        $count = (int) $enrolled->fetchColumn();

        $status = $count < $capacity ? 'enrolled' : 'waitlisted';

        $stmt = $db->prepare(
            "INSERT INTO enrollment (member_id, session_id, status)
             VALUES (:member_id, :session_id, :status)
             ON CONFLICT (member_id, session_id) DO UPDATE SET
               status = CASE
                 WHEN enrollment.status = 'cancelled' THEN excluded.status
                 ELSE enrollment.status
               END"
        );
        $stmt->execute([
            ':member_id' => $memberId,
            ':session_id' => $sessionId,
            ':status' => $status,
        ]);

        return $status;
    }

    public static function getStatusForMember(PDO $db, int $memberId, int $sessionId): ?string
    {
        $stmt = $db->prepare(
            "SELECT status FROM enrollment
             WHERE member_id = :member_id AND session_id = :session_id
               AND status NOT IN ('cancelled')"
        );
        $stmt->execute([':member_id' => $memberId, ':session_id' => $sessionId]);
        $row = $stmt->fetchColumn();
        return $row !== false ? (string) $row : null;
    }

    public static function getSessionsForWeekAdmin(PDO $db, string $weekStart, string $weekEnd): array
    {
        $stmt = $db->prepare(
            "SELECT
                cs.id AS session_id,
                cs.datetime,
                cs.room,
                cs.capacity,
                c.id AS class_id,
                c.name AS class_name,
                c.intensity,
                c.duration_minutes,
                c.description,
                ct.id AS type_id,
                ct.name AS type_name,
                u.user_id AS trainer_id,
                u.name AS trainer_name,
                c.is_featured,
                COUNT(CASE WHEN e.status = 'enrolled'   THEN 1 END) AS enrolled_count,
                COUNT(CASE WHEN e.status = 'waitlisted' THEN 1 END) AS waitlisted_count,
                (SELECT ROUND(AVG(r.rating), 1) FROM review r WHERE r.class_id = c.id) AS avg_rating,
                (SELECT COUNT(*) FROM review r WHERE r.class_id = c.id) AS review_count
             FROM class_session cs
             JOIN class c ON c.id = cs.class_id
             LEFT JOIN class_type ct ON ct.id = c.type_id
             LEFT JOIN user u ON u.user_id = c.trainer_id
             LEFT JOIN enrollment e ON e.session_id = cs.id
             WHERE cs.datetime >= :start AND cs.datetime < :end
             GROUP BY cs.id
             ORDER BY cs.datetime ASC"
        );
        $stmt->execute([':start' => $weekStart, ':end' => $weekEnd]);
        return $stmt->fetchAll();
    }

    public static function getSessionsForWeek(PDO $db, int $memberId, string $weekStart, string $weekEnd): array
    {
        $stmt = $db->prepare(
            "SELECT
                cs.id AS session_id,
                cs.datetime,
                cs.room,
                cs.capacity,
                c.id AS class_id,
                c.name AS class_name,
                c.intensity,
                c.duration_minutes,
                ct.name AS type_name,
                u.name AS trainer_name,
                COUNT(CASE WHEN e.status = 'enrolled' THEN 1 END) AS enrolled_count,
                MAX(CASE WHEN e.member_id = :member_id AND e.status NOT IN ('cancelled') THEN e.status END) AS member_status,
                (SELECT ROUND(AVG(r.rating), 1) FROM review r WHERE r.class_id = c.id) AS avg_rating,
                (SELECT COUNT(*) FROM review r WHERE r.class_id = c.id) AS review_count
             FROM class_session cs
             JOIN class c ON c.id = cs.class_id
             LEFT JOIN class_type ct ON ct.id = c.type_id
             LEFT JOIN user u ON u.user_id = c.trainer_id
             LEFT JOIN enrollment e ON e.session_id = cs.id
             WHERE cs.datetime >= :start AND cs.datetime < :end
             GROUP BY cs.id
             ORDER BY cs.datetime ASC"
        );
        $stmt->execute([
            ':member_id' => $memberId,
            ':start' => $weekStart,
            ':end' => $weekEnd,
        ]);
        return $stmt->fetchAll();
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
