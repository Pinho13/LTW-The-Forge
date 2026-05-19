<?php
declare(strict_types=1);

class GymVisit
{
    public static function getWeeklyStreak(PDO $db, int $memberId): int
    {
        $stmt = $db->prepare(
            "SELECT DISTINCT date(entered_at, 'localtime', '-' || strftime('%w', entered_at, 'localtime') || ' days') AS week_start
             FROM gym_visit
             WHERE member_id = :member_id
             ORDER BY week_start DESC"
        );
        $stmt->execute([':member_id' => $memberId]);
        $weeks = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($weeks)) {
            return 0;
        }

        $dow = (int) date('w'); // 0 = Sunday
        $thisWeek = date('Y-m-d', strtotime("-{$dow} days"));
        $lastWeek = date('Y-m-d', strtotime('-' . ($dow + 7) . ' days'));

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
