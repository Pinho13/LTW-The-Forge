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
}
