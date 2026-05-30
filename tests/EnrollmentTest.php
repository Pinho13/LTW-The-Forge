<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/Enrollment.class.php';

class EnrollmentTest extends TestCase
{
    private PDO $db;
    private int $unitId;

    protected function setUp(): void
    {
        date_default_timezone_set('UTC');

        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
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

        $this->db->exec("INSERT INTO equipment (name) VALUES ('Treadmill');");
        $equipmentId = (int) $this->db->lastInsertId();
        $this->db->exec("INSERT INTO equipment_unit (equipment_id) VALUES ({$equipmentId});");
        $this->unitId = (int) $this->db->lastInsertId();
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

    private function reserve(int $memberId, string $startDatetime, string $endDatetime): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment_reservation (member_id, unit_id, start_datetime, end_datetime)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$memberId, $this->unitId, $startDatetime, $endDatetime]);
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

    public function testCountUpcomingReturnsZeroForNoReservations(): void
    {
        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingCountsFutureReservations(): void
    {
        $start = date('Y-m-d H:i:s', strtotime('+1 day'));
        $end = date('Y-m-d H:i:s', strtotime('+1 day +1 hour'));
        $this->reserve(1, $start, $end);

        $this->assertSame(1, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingIgnoresPastReservations(): void
    {
        $start = date('Y-m-d H:i:s', strtotime('-1 day'));
        $end = date('Y-m-d H:i:s', strtotime('-1 day +1 hour'));
        $this->reserve(1, $start, $end);

        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingOnlyCountsCorrectUser(): void
    {
        $start = date('Y-m-d H:i:s', strtotime('+1 day'));
        $end = date('Y-m-d H:i:s', strtotime('+1 day +1 hour'));
        $this->reserve(2, $start, $end); // user 2, not user 1

        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingCountsMultipleFutureReservations(): void
    {
        $s1 = date('Y-m-d H:i:s', strtotime('+1 day'));
        $e1 = date('Y-m-d H:i:s', strtotime('+1 day +1 hour'));
        $s2 = date('Y-m-d H:i:s', strtotime('+5 days'));
        $e2 = date('Y-m-d H:i:s', strtotime('+5 days +1 hour'));
        $s3 = date('Y-m-d H:i:s', strtotime('+10 days'));
        $e3 = date('Y-m-d H:i:s', strtotime('+10 days +1 hour'));
        $this->reserve(1, $s1, $e1);
        $this->reserve(1, $s2, $e2);
        $this->reserve(1, $s3, $e3);

        $this->assertSame(3, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountUpcomingMixedPastAndFutureReservations(): void
    {
        $pastStart   = date('Y-m-d H:i:s', strtotime('-1 day'));
        $pastEnd     = date('Y-m-d H:i:s', strtotime('-1 day +1 hour'));
        $futureStart = date('Y-m-d H:i:s', strtotime('+1 day'));
        $futureEnd   = date('Y-m-d H:i:s', strtotime('+1 day +1 hour'));
        $this->reserve(1, $pastStart, $pastEnd);
        $this->reserve(1, $futureStart, $futureEnd);

        $this->assertSame(1, Enrollment::countUpcoming($this->db, 1));
    }

    public function testCountThisMonthIgnoresFutureMonthSessions(): void
    {
        $nextMonth = date('Y-m-d 10:00:00', strtotime('first day of next month'));
        $sessionId = $this->insertSession($nextMonth);
        $this->enroll(1, $sessionId);

        $this->assertSame(0, Enrollment::countEnrolledThisMonth($this->db, 1));
    }

    // SQL uses >, so a reservation at exactly now is NOT counted as upcoming
    public function testCountUpcomingExcludesReservationAtExactlyNow(): void
    {
        $now = date('Y-m-d H:i:s');
        $end = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $this->reserve(1, $now, $end);

        $this->assertSame(0, Enrollment::countUpcoming($this->db, 1));
    }

    // --- findNextForMember ---

    public function testFindNextReturnsNullWhenNoEnrollments(): void
    {
        $this->assertNull(Enrollment::findNextForMember($this->db, 1));
    }

    public function testFindNextReturnsNullWhenOnlyPastSessions(): void
    {
        $sessionId = $this->insertSession(date('Y-m-d H:i:s', strtotime('-1 day')));
        $this->enroll(1, $sessionId);

        $this->assertNull(Enrollment::findNextForMember($this->db, 1));
    }

    public function testFindNextReturnsSingleFutureSession(): void
    {
        $future = date('Y-m-d H:i:s', strtotime('+1 day'));
        $sessionId = $this->insertSession($future);
        $this->enroll(1, $sessionId);

        $result = Enrollment::findNextForMember($this->db, 1);

        $this->assertNotNull($result);
        $this->assertSame('Morning Yoga', $result['class_name']);
        $this->assertSame('Room A', $result['room']);
        $this->assertSame($future, $result['datetime']);
    }

    public function testFindNextReturnsClosestWhenMultipleFutureSessions(): void
    {
        $closer  = date('Y-m-d H:i:s', strtotime('+1 day'));
        $further = date('Y-m-d H:i:s', strtotime('+5 days'));

        $s1 = $this->insertSession($further);
        $s2 = $this->insertSession($closer);
        $this->enroll(1, $s1);
        $this->enroll(1, $s2);

        $result = Enrollment::findNextForMember($this->db, 1);

        $this->assertSame($closer, $result['datetime']);
    }

    // --- getRecentActivity ---

    private function enrollWithStatus(int $memberId, int $sessionId, string $status): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO enrollment (member_id, session_id, status) VALUES (?, ?, ?)"
        );
        $stmt->execute([$memberId, $sessionId, $status]);
    }

    public function testRecentActivityReturnsEmptyWhenNoEnrollments(): void
    {
        $this->assertSame([], Enrollment::getRecentActivity($this->db, 1));
    }

    public function testRecentActivityReturnsCompletedSession(): void
    {
        $sessionId = $this->insertSession(date('Y-m-d H:i:s', strtotime('-1 day')));
        $this->enrollWithStatus(1, $sessionId, 'completed');

        $result = Enrollment::getRecentActivity($this->db, 1);

        $this->assertCount(1, $result);
        $this->assertSame('completed', $result[0]['status']);
    }

    public function testRecentActivityReturnsMissedSession(): void
    {
        $sessionId = $this->insertSession(date('Y-m-d H:i:s', strtotime('-1 day')));
        $this->enrollWithStatus(1, $sessionId, 'missed');

        $result = Enrollment::getRecentActivity($this->db, 1);

        $this->assertCount(1, $result);
        $this->assertSame('missed', $result[0]['status']);
    }

    public function testRecentActivityIgnoresFutureSessions(): void
    {
        $sessionId = $this->insertSession(date('Y-m-d H:i:s', strtotime('+1 day')));
        $this->enrollWithStatus(1, $sessionId, 'completed');

        $this->assertSame([], Enrollment::getRecentActivity($this->db, 1));
    }

    public function testRecentActivityIgnoresEnrolledStatus(): void
    {
        $sessionId = $this->insertSession(date('Y-m-d H:i:s', strtotime('-1 day')));
        $this->enroll(1, $sessionId, 'enrolled');

        $this->assertSame([], Enrollment::getRecentActivity($this->db, 1));
    }

    public function testRecentActivityIgnoresSessionsOlderThanTwoWeeks(): void
    {
        $sessionId = $this->insertSession(date('Y-m-d H:i:s', strtotime('-15 days')));
        $this->enrollWithStatus(1, $sessionId, 'completed');

        $this->assertSame([], Enrollment::getRecentActivity($this->db, 1));
    }

    public function testRecentActivityLimitsToSevenMostRecent(): void
    {
        for ($i = 1; $i <= 9; $i++) {
            $sessionId = $this->insertSession(date('Y-m-d H:i:s', strtotime("-{$i} day")));
            $this->enrollWithStatus(1, $sessionId, 'completed');
        }

        $this->assertCount(7, Enrollment::getRecentActivity($this->db, 1));
    }

    public function testRecentActivityIsOrderedMostRecentFirst(): void
    {
        $older = date('Y-m-d H:i:s', strtotime('-3 days'));
        $newer = date('Y-m-d H:i:s', strtotime('-1 day'));

        $s1 = $this->insertSession($older);
        $s2 = $this->insertSession($newer);
        $this->enrollWithStatus(1, $s1, 'completed');
        $this->enrollWithStatus(1, $s2, 'missed');

        $result = Enrollment::getRecentActivity($this->db, 1);

        $this->assertSame($newer, $result[0]['datetime']);
        $this->assertSame($older, $result[1]['datetime']);
    }
}
