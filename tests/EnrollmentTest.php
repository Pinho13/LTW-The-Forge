<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/Enrollment.class.php';

class EnrollmentTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        date_default_timezone_set('UTC');

        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->db->exec($schema);

        // Seed one member and one class (needed for FK constraints)
        $this->db->exec("
            INSERT INTO user (name, username, email, password_hash, role) VALUES
                ('User One', 'user1', 'user1@test.com', 'hash', 'member'),
                ('User Two', 'user2', 'user2@test.com', 'hash', 'member');
            INSERT INTO class_type (name) VALUES ('Yoga');
            INSERT INTO class (name, type_id, description, duration_minutes, intensity) VALUES
                ('Morning Yoga', 1, '', 60, 2);
        ");
    }

    private function insertSession(string $datetime): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO class_session (class_id, datetime, room, capacity) VALUES (1, ?, 'Room A', 10)"
        );
        $stmt->execute([$datetime]);
        return (int) $this->db->lastInsertId();
    }

    private function enroll(int $memberId, int $sessionId, string $status = 'enrolled'): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO enrollment (member_id, session_id, status) VALUES (?, ?, ?)"
        );
        $stmt->execute([$memberId, $sessionId, $status]);
    }

    // --- countEnrolledThisMonth ---

    public function testCountThisMonthReturnsZeroForNoEnrollments(): void
    {
        $this->assertSame(0, Enrollment::countEnrolledThisMonth($this->db, 1));
    }

    public function testCountThisMonthCountsEnrolledSessionsInCurrentMonth(): void
    {
        $thisMonth = date('Y-m-15 10:00:00');
        $sessionId = $this->insertSession($thisMonth);
        $this->enroll(1, $sessionId);

        $this->assertSame(1, Enrollment::countEnrolledThisMonth($this->db, 1));
    }

    public function testCountThisMonthIgnoresPastMonthSessions(): void
    {
        $lastMonth = date('Y-m-d 10:00:00', strtotime('first day of last month'));
        $sessionId = $this->insertSession($lastMonth);
        $this->enroll(1, $sessionId);

        $this->assertSame(0, Enrollment::countEnrolledThisMonth($this->db, 1));
    }

    public function testCountThisMonthIgnoresCancelledStatus(): void
    {
        $thisMonth = date('Y-m-15 10:00:00');
        $sessionId = $this->insertSession($thisMonth);
        $this->enroll(1, $sessionId, 'cancelled');

        $this->assertSame(0, Enrollment::countEnrolledThisMonth($this->db, 1));
    }

    public function testCountThisMonthIgnoresWaitlistedStatus(): void
    {
        $thisMonth = date('Y-m-15 10:00:00');
        $sessionId = $this->insertSession($thisMonth);
        $this->enroll(1, $sessionId, 'waitlisted');

        $this->assertSame(0, Enrollment::countEnrolledThisMonth($this->db, 1));
    }

    public function testCountThisMonthOnlyCountsCorrectUser(): void
    {
        $thisMonth = date('Y-m-15 10:00:00');
        $sessionId = $this->insertSession($thisMonth);
        $this->enroll(2, $sessionId); // user 2, not user 1

        $this->assertSame(0, Enrollment::countEnrolledThisMonth($this->db, 1));
    }

    public function testCountThisMonthCountsMultipleSessions(): void
    {
        $thisMonth = date('Y-m');
        $s1 = $this->insertSession("{$thisMonth}-10 08:00:00");
        $s2 = $this->insertSession("{$thisMonth}-15 10:00:00");
        $s3 = $this->insertSession("{$thisMonth}-20 18:00:00");
        $this->enroll(1, $s1);
        $this->enroll(1, $s2);
        $this->enroll(1, $s3);

        $this->assertSame(3, Enrollment::countEnrolledThisMonth($this->db, 1));
    }

    // --- countUpcoming ---

    public function testCountUpcomingReturnsZeroForNoEnrollments(): void
    {
        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingCountsFutureSessions(): void
    {
        $future = date('Y-m-d H:i:s', strtotime('+1 day'));
        $sessionId = $this->insertSession($future);
        $this->enroll(1, $sessionId);

        $this->assertSame(1, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingIgnoresPastSessions(): void
    {
        $past = date('Y-m-d H:i:s', strtotime('-1 day'));
        $sessionId = $this->insertSession($past);
        $this->enroll(1, $sessionId);

        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingIgnoresCancelledStatus(): void
    {
        $future = date('Y-m-d H:i:s', strtotime('+1 day'));
        $sessionId = $this->insertSession($future);
        $this->enroll(1, $sessionId, 'cancelled');

        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingIgnoresWaitlistedStatus(): void
    {
        $future = date('Y-m-d H:i:s', strtotime('+1 day'));
        $sessionId = $this->insertSession($future);
        $this->enroll(1, $sessionId, 'waitlisted');

        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingOnlyCountsCorrectUser(): void
    {
        $future = date('Y-m-d H:i:s', strtotime('+1 day'));
        $sessionId = $this->insertSession($future);
        $this->enroll(2, $sessionId); // user 2, not user 1

        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingCountsMultipleFutureSessions(): void
    {
        $s1 = $this->insertSession(date('Y-m-d H:i:s', strtotime('+1 day')));
        $s2 = $this->insertSession(date('Y-m-d H:i:s', strtotime('+5 days')));
        $s3 = $this->insertSession(date('Y-m-d H:i:s', strtotime('+10 days')));
        $this->enroll(1, $s1);
        $this->enroll(1, $s2);
        $this->enroll(1, $s3);

        $this->assertSame(3, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingMixedPastAndFuture(): void
    {
        $past   = $this->insertSession(date('Y-m-d H:i:s', strtotime('-1 day')));
        $future = $this->insertSession(date('Y-m-d H:i:s', strtotime('+1 day')));
        $this->enroll(1, $past);
        $this->enroll(1, $future);

        $this->assertSame(1, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountThisMonthIgnoresFutureMonthSessions(): void
    {
        $nextMonth = date('Y-m-d 10:00:00', strtotime('first day of next month'));
        $sessionId = $this->insertSession($nextMonth);
        $this->enroll(1, $sessionId);

        $this->assertSame(0, Enrollment::countEnrolledThisMonth($this->db, 1));
    }

    // SQL uses >, so a session at exactly now is NOT counted as upcoming
    public function testCountUpcomingExcludesSessionAtExactlyNow(): void
    {
        $now = date('Y-m-d H:i:s');
        $sessionId = $this->insertSession($now);
        $this->enroll(1, $sessionId);

        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }
}
