<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/AdminAnalytics.class.php';

class AdminAnalyticsTest extends TestCase
{
    private PDO $db;
    private int $trainerId;
    private int $memberId;
    private int $yogaTypeId;
    private int $boxingTypeId;
    private int $yogaClassId;
    private int $boxingClassId;
    private int $yogaSessionId;
    private int $boxingSessionId;
    private int $bikeUnitId;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->trainerId = $this->insertUser('Trainer One', 'trainer1', 'trainer1@test.com', 'trainer', '-20 days');
        $this->memberId = $this->insertUser('Member One', 'member1', 'member1@test.com', 'member', '-5 days');

        $this->yogaTypeId = $this->insertClassType('Yoga');
        $this->boxingTypeId = $this->insertClassType('Boxing');

        $this->yogaClassId = $this->insertClass('Morning Yoga', $this->yogaTypeId, $this->trainerId);
        $this->boxingClassId = $this->insertClass('Boxing Basics', $this->boxingTypeId, $this->trainerId);

        $this->yogaSessionId = $this->insertSession($this->yogaClassId, date('Y-m-d H:i:s', strtotime('-2 days')), 'Studio 1', 10);
        $this->boxingSessionId = $this->insertSession($this->boxingClassId, date('Y-m-d H:i:s', strtotime('-1 day')), 'Studio 2', 10);

        $this->insertEnrollment($this->memberId, $this->yogaSessionId, 'completed');
        $this->insertEnrollment($this->memberId, $this->boxingSessionId, 'enrolled');
        $this->insertReview($this->memberId, $this->yogaClassId, 4, 'Great');
        $this->insertReview($this->memberId, $this->boxingClassId, 5, 'Amazing');

        $equipmentId = $this->insertEquipment('Bike', 'cardio');
        $this->bikeUnitId = $this->insertUnit($equipmentId, 'B1', 'available');
        $this->insertReservation($this->memberId, $this->bikeUnitId, date('Y-m-d H:i:s', strtotime('-1 day')), date('Y-m-d H:i:s', strtotime('-1 day +1 hour')));

        $this->insertSubscription($this->memberId, 'active', date('Y-m-d', strtotime('-20 days')), date('Y-m-d', strtotime('+10 days')));
        $this->insertSubscription($this->memberId, 'frozen', date('Y-m-d', strtotime('-30 days')), date('Y-m-d', strtotime('+20 days')));
        $this->insertSubscription($this->memberId, 'expired', date('Y-m-d', strtotime('-60 days')), date('Y-m-d', strtotime('-30 days')));
    }

    private function insertUser(string $name, string $username, string $email, string $role, string $createdModifier): int
    {
        $createdAt = $this->db->query("SELECT datetime('now','localtime','{$createdModifier}')")->fetchColumn();
        $stmt = $this->db->prepare(
            "INSERT INTO user (name, username, email, password_hash, role, created_at, is_active)
             VALUES (:name, :username, :email, 'hash', :role, :created_at, 1)"
        );
        $stmt->execute([
            ':name' => $name,
            ':username' => $username,
            ':email' => $email,
            ':role' => $role,
            ':created_at' => $createdAt,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function insertClassType(string $name): int
    {
        $stmt = $this->db->prepare("INSERT INTO class_type (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        return (int) $this->db->lastInsertId();
    }

    private function insertClass(string $name, int $typeId, int $trainerId): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO class (name, type_id, description, duration_minutes, intensity, trainer_id)
             VALUES (:name, :type_id, '', 60, 3, :trainer_id)"
        );
        $stmt->execute([
            ':name' => $name,
            ':type_id' => $typeId,
            ':trainer_id' => $trainerId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function insertSession(int $classId, string $datetime, string $room, int $capacity): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO class_session (class_id, datetime, room, capacity)
             VALUES (:class_id, :datetime, :room, :capacity)"
        );
        $stmt->execute([
            ':class_id' => $classId,
            ':datetime' => $datetime,
            ':room' => $room,
            ':capacity' => $capacity,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function insertEnrollment(int $memberId, int $sessionId, string $status): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO enrollment (member_id, session_id, status)
             VALUES (:member_id, :session_id, :status)"
        );
        $stmt->execute([
            ':member_id' => $memberId,
            ':session_id' => $sessionId,
            ':status' => $status,
        ]);
    }

    private function insertReview(int $memberId, int $classId, int $rating, string $comment): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO review (member_id, class_id, rating, comment)
             VALUES (:member_id, :class_id, :rating, :comment)"
        );
        $stmt->execute([
            ':member_id' => $memberId,
            ':class_id' => $classId,
            ':rating' => $rating,
            ':comment' => $comment,
        ]);
    }

    private function insertEquipment(string $name, string $type): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment (name, type, description, photo, default_w, default_h)
             VALUES (:name, :type, '', NULL, 50, 40)"
        );
        $stmt->execute([':name' => $name, ':type' => $type]);
        return (int) $this->db->lastInsertId();
    }

    private function insertUnit(int $equipmentId, string $identifier, string $status): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment_unit (equipment_id, identifier, status)
             VALUES (:equipment_id, :identifier, :status)"
        );
        $stmt->execute([
            ':equipment_id' => $equipmentId,
            ':identifier' => $identifier,
            ':status' => $status,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function insertReservation(int $memberId, int $unitId, string $start, string $end): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment_reservation (member_id, unit_id, start_datetime, end_datetime)
             VALUES (:member_id, :unit_id, :start_datetime, :end_datetime)"
        );
        $stmt->execute([
            ':member_id' => $memberId,
            ':unit_id' => $unitId,
            ':start_datetime' => $start,
            ':end_datetime' => $end,
        ]);
    }

    private function insertSubscription(int $memberId, string $status, string $start, string $end): void
    {
        $planId = $this->db->query("SELECT id FROM membership_plan WHERE name='Basic'")->fetchColumn();
        if (!$planId) {
            $this->db->exec("INSERT INTO membership_plan (name, price, description, max_classes_per_month) VALUES ('Basic', 10.0, '', 10)");
            $planId = $this->db->lastInsertId();
        }

        $stmt = $this->db->prepare(
            "INSERT INTO member_subscription (member_id, plan_id, start_date, end_date, status)
             VALUES (:member_id, :plan_id, :start_date, :end_date, :status)"
        );
        $stmt->execute([
            ':member_id' => $memberId,
            ':plan_id' => $planId,
            ':start_date' => $start,
            ':end_date' => $end,
            ':status' => $status,
        ]);
    }

    public function testGetTopClassesOrdersByEnrollment(): void
    {
        $rows = AdminAnalytics::getTopClasses($this->db, 2);
        $this->assertCount(2, $rows);
        $this->assertSame('Morning Yoga', $rows[0]['name']);
        $this->assertSame(1, (int) $rows[0]['total_enrolled']);
        $this->assertSame(1, (int) $rows[0]['review_count']);
    }

    public function testGetEnrollmentByMonthReturnsRecentMonths(): void
    {
        $rows = AdminAnalytics::getEnrollmentByMonth($this->db, 6);
        $this->assertNotEmpty($rows);
        $this->assertArrayHasKey('month', $rows[0]);
        $this->assertArrayHasKey('enrollments', $rows[0]);
    }

    public function testGetEquipmentUsageAggregatesCounts(): void
    {
        $rows = AdminAnalytics::getEquipmentUsage($this->db);
        $this->assertCount(1, $rows);
        $this->assertSame('Bike', $rows[0]['equipment_name']);
        $this->assertSame(1, (int) $rows[0]['reservation_count']);
        $this->assertSame(1, (int) $rows[0]['unit_count']);
    }

    public function testGetMemberRetentionCountsStatuses(): void
    {
        $stats = AdminAnalytics::getMemberRetention($this->db);
        $this->assertSame(1, $stats['active']);
        $this->assertSame(1, $stats['frozen']);
        $this->assertSame(1, $stats['cancelled']);
    }

    public function testGetClassTypeDistributionCountsEnrollments(): void
    {
        $rows = AdminAnalytics::getClassTypeDistribution($this->db);
        $this->assertCount(2, $rows);
        $types = array_column($rows, 'type');
        $this->assertContains('Yoga', $types);
        $this->assertContains('Boxing', $types);
    }

    public function testGetGymVisitsByDayReturnsSevenDays(): void
    {
        $rows = AdminAnalytics::getGymVisitsByDay($this->db);
        $this->assertCount(7, $rows);
        $this->assertSame(7, count($rows));
    }

    public function testGetNewMembersPerMonthReturnsRecentMembers(): void
    {
        $rows = AdminAnalytics::getNewMembersPerMonth($this->db, 6);
        $this->assertNotEmpty($rows);
        $this->assertSame(1, (int) $rows[0]['new_members']);
    }

    public function testGetTopTrainersReturnsTrainerStats(): void
    {
        $rows = AdminAnalytics::getTopTrainers($this->db, 3);
        $this->assertCount(1, $rows);
        $this->assertSame('Trainer One', $rows[0]['name']);
        $this->assertSame(2, (int) $rows[0]['sessions_taught']);
    }
}
