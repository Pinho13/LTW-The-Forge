<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/GymVisit.class.php';

class GymVisitTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        date_default_timezone_set('UTC');

        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->db->exec("
            INSERT INTO user (name, username, email, password_hash, role) VALUES
                ('User One', 'user1', 'user1@test.com', 'hash', 'member'),
                ('User Two', 'user2', 'user2@test.com', 'hash', 'member');
        ");
    }

    private function visit(int $memberId, string $date): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO gym_visit (member_id, entered_at, status) VALUES (?, ?, 'left')"
        );
        $stmt->execute([$memberId, $date . ' 10:00:00']);
    }

    // Returns a date string N weeks ago (always lands in the correct week boundary)
    private function weeksAgo(int $n): string
    {
        return date('Y-m-d', strtotime("-{$n} week"));
    }

    // Returns a date guaranteed to be in the same week as today (Sun–Sat)
    private function thisWeekDay(int $offsetFromSunday): string
    {
        $dow = (int) date('w'); // 0 = Sunday
        return date('Y-m-d', strtotime("-{$dow} days +{$offsetFromSunday} days"));
    }

    public function testNoVisitsReturnsZero(): void
    {
        $this->assertSame(0, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testVisitedOnlyThisWeekReturnsOne(): void
    {
        $this->visit(1, date('Y-m-d'));
        $this->assertSame(1, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testVisitedOnlyLastWeekReturnsOne(): void
    {
        // Streak stays alive — user hasn't missed a full week yet
        $this->visit(1, $this->weeksAgo(1));
        $this->assertSame(1, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testVisitedOnlyTwoWeeksAgoReturnsZero(): void
    {
        // Gap of a full week — streak is dead
        $this->visit(1, $this->weeksAgo(2));
        $this->assertSame(0, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testThisWeekAndLastWeekReturnsTwoStreak(): void
    {
        $this->visit(1, date('Y-m-d'));
        $this->visit(1, $this->weeksAgo(1));
        $this->assertSame(2, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testGapBreaksStreakIgnoringOlderVisits(): void
    {
        // Last week visited, week before missed, 3 weeks ago visited — streak is 1
        $this->visit(1, $this->weeksAgo(1));
        $this->visit(1, $this->weeksAgo(3));
        $this->assertSame(1, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testMultipleVisitsSameWeekCountAsOne(): void
    {
        // Use Sunday and Wednesday of this week — guaranteed same week regardless of today's day
        $this->visit(1, $this->thisWeekDay(0)); // this week's Sunday
        $this->visit(1, $this->thisWeekDay(3)); // this week's Wednesday
        $this->assertSame(1, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testVisitOnSundayStartsNewWeek(): void
    {
        // Sunday is the first day of the week — a visit on Sunday + Wednesday should be 1, not 2
        $this->visit(1, $this->thisWeekDay(0)); // this Sunday
        $this->visit(1, $this->thisWeekDay(4)); // this Thursday
        $this->assertSame(1, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testVisitOnSaturdayAndFollowingSundayAreDifferentWeeks(): void
    {
        // Saturday ends the week; the very next day (Sunday) starts a new one
        $dow = (int) date('w');
        $thisSunday   = date('Y-m-d', strtotime("-{$dow} days"));
        $lastSaturday = date('Y-m-d', strtotime("-{$dow} days -1 day"));

        $this->visit(1, $thisSunday);   // this week
        $this->visit(1, $lastSaturday); // last week
        $this->assertSame(2, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testStreakAboveSix(): void
    {
        for ($i = 0; $i <= 6; $i++) {
            $this->visit(1, $this->weeksAgo($i));
        }
        $this->assertSame(7, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testStreakOnlyCountsCorrectUser(): void
    {
        $this->visit(2, date('Y-m-d')); // user 2, not user 1
        $this->assertSame(0, GymVisit::getWeeklyStreak($this->db, 1));
    }

    public function testCurrentlyInGymCountsAsVisit(): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO gym_visit (member_id, entered_at, status) VALUES (?, ?, 'in_gym')"
        );
        $stmt->execute([1, date('Y-m-d') . ' 09:00:00']);
        $this->assertSame(1, GymVisit::getWeeklyStreak($this->db, 1));
    }
}
