<?php
declare(strict_types=1);

class GymVisit
{
    public static function getWeeklyStreak(PDO $db, int $memberId): int
    {
        // A week counts if the member attended a class OR had an equipment reservation
        // that started in the past. Only past/current activity counts.
        $stmt = $db->prepare(
            "SELECT DISTINCT
                date(activity_dt, 'localtime', '-' || strftime('%w', activity_dt, 'localtime') || ' days') AS week_start
             FROM (
                 SELECT cs.datetime AS activity_dt
                 FROM enrollment e
                 JOIN class_session cs ON cs.id = e.session_id
                 WHERE e.member_id = :mid1
                   AND e.status = 'attended'
                 UNION ALL
                 SELECT start_datetime AS activity_dt
                 FROM equipment_reservation
                 WHERE member_id = :mid2
                   AND start_datetime <= datetime('now', 'localtime')
             )
             ORDER BY week_start DESC"
        );
        $stmt->execute([':mid1' => $memberId, ':mid2' => $memberId]);
        $weeks = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($weeks)) {
            return 0;
        }

        $dow      = (int) date('w');
        $thisWeek = date('Y-m-d', strtotime("-{$dow} days"));
        $lastWeek = date('Y-m-d', strtotime('-' . ($dow + 7) . ' days'));

        // Streak resets if most recent active week isn't this week or last week
        if ($weeks[0] !== $thisWeek && $weeks[0] !== $lastWeek) {
            return 0;
        }

        $streak = 1;
        for ($i = 1; $i < count($weeks); $i++) {
            $expected = date('Y-m-d', strtotime('-7 days', strtotime($weeks[$i - 1])));
            if ($weeks[$i] === $expected) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}
