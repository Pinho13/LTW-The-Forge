<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/AdminLog.class.php';

class AdminLogTest extends TestCase
{
    private PDO $db;
    private int $adminId;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->adminId = $this->insertUser('Admin', 'admin', 'admin@test.com', 'admin', true, date('Y-m-d H:i:s'));
    }

    private function insertUser(
        string $name,
        string $username,
        string $email,
        string $role,
        bool $isActive,
        string $createdAt
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO user (name, username, email, password_hash, role, is_active, created_at)
             VALUES (:name, :username, :email, 'hash', :role, :active, :created_at)"
        );
        $stmt->execute([
            ':name' => $name,
            ':username' => $username,
            ':email' => $email,
            ':role' => $role,
            ':active' => $isActive ? 1 : 0,
            ':created_at' => $createdAt,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function insertClass(?int $trainerId): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO class (name, type_id, description, duration_minutes, intensity, trainer_id)
             VALUES ('Class', NULL, '', 60, 3, :trainer_id)"
        );
        $stmt->execute([':trainer_id' => $trainerId]);
        return (int) $this->db->lastInsertId();
    }

    private function insertSession(int $classId, string $datetime, int $capacity): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO class_session (class_id, datetime, room, capacity)
             VALUES (:class_id, :datetime, 'Studio', :capacity)"
        );
        $stmt->execute([
            ':class_id' => $classId,
            ':datetime' => $datetime,
            ':capacity' => $capacity,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function insertEnrollment(int $memberId, int $sessionId, string $status = 'enrolled'): void
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

    private function insertEquipment(string $name): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment (name, type, description, photo, default_w, default_h)
             VALUES (:name, 'cardio', '', NULL, 50, 40)"
        );
        $stmt->execute([':name' => $name]);
        return (int) $this->db->lastInsertId();
    }

    private function insertUnit(int $equipmentId, string $status): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment_unit (equipment_id, status)
             VALUES (:equipment_id, :status)"
        );
        $stmt->execute([
            ':equipment_id' => $equipmentId,
            ':status' => $status,
        ]);
    }

    private function insertSubscription(int $memberId, string $status, string $startDate, string $endDate): void
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
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':status' => $status,
        ]);
    }

    public function testWriteAndGetAllOrdersByCreatedAt(): void
    {
        AdminLog::write($this->db, $this->adminId, 'CREATE', 'First action');
        AdminLog::write($this->db, $this->adminId, 'UPDATE', 'Second action');

        $this->db->exec("UPDATE admin_log SET created_at = '2025-01-01 00:00:00' WHERE description = 'First action'");
        $this->db->exec("UPDATE admin_log SET created_at = '2025-02-01 00:00:00' WHERE description = 'Second action'");

        $rows = AdminLog::getAll($this->db);

        $this->assertCount(2, $rows);
        $this->assertSame('Second action', $rows[0]['description']);
        $this->assertSame('Admin', $rows[0]['admin_name']);
    }

    public function testGetRecentRespectsLimit(): void
    {
        AdminLog::write($this->db, $this->adminId, 'CREATE', 'First action');
        AdminLog::write($this->db, $this->adminId, 'UPDATE', 'Second action');
        AdminLog::write($this->db, $this->adminId, 'DELETE', 'Third action');

        $this->db->exec("UPDATE admin_log SET created_at = '2025-01-01 00:00:00' WHERE description = 'First action'");
        $this->db->exec("UPDATE admin_log SET created_at = '2025-02-01 00:00:00' WHERE description = 'Second action'");
        $this->db->exec("UPDATE admin_log SET created_at = '2025-03-01 00:00:00' WHERE description = 'Third action'");

        $rows = AdminLog::getRecent($this->db, 2);

        $this->assertCount(2, $rows);
        $this->assertSame('Third action', $rows[0]['description']);
    }

    public function testGetStatsCountsExpectedValues(): void
    {
        $this->insertUser('Member Current', 'member1', 'member1@test.com', 'member', true, date('Y-m-d H:i:s'));
        $this->insertUser('Member Old', 'member2', 'member2@test.com', 'member', true, date('Y-m-d H:i:s', strtotime('-40 days')));
        $this->insertUser('Member Inactive', 'member3', 'member3@test.com', 'member', false, date('Y-m-d H:i:s', strtotime('-40 days')));

        $this->insertUser('Trainer Active', 'trainer1', 'trainer1@test.com', 'trainer', true, date('Y-m-d H:i:s'));
        $this->insertUser('Trainer Inactive', 'trainer2', 'trainer2@test.com', 'trainer', false, date('Y-m-d H:i:s'));

        $draftClassId = $this->insertClass(null);
        $weekSessionDate = (string) $this->db->query("SELECT datetime('now','+1 minute')")->fetchColumn();
        $this->insertSession($draftClassId, $weekSessionDate, 10);

        $capacityClassId = $this->insertClass($this->adminId);
        $capacitySessionDate = date('Y-m-d H:i:s', strtotime('+10 days'));
        $capacitySessionId = $this->insertSession($capacityClassId, $capacitySessionDate, 1);
        $memberId = $this->insertUser('Member Capacity', 'member4', 'member4@test.com', 'member', true, date('Y-m-d H:i:s'));
        $this->insertEnrollment($memberId, $capacitySessionId);

        $equipmentId = $this->insertEquipment('Bike');
        $this->insertUnit($equipmentId, 'available');
        $this->insertUnit($equipmentId, 'maintenance');

        $stats = AdminLog::getStats($this->db);

        $this->assertSame(3, $stats['active_members']);
        $this->assertSame(2, $stats['new_members_month']);
        $this->assertSame(1, $stats['active_trainers']);
        $this->assertSame(2, $stats['total_trainers']);
        $this->assertSame(1, $stats['sessions_this_week']);
        $this->assertSame(1, $stats['sessions_draft']);
        $this->assertSame(1, $stats['equipment_ready']);
        $this->assertSame(2, $stats['equipment_total']);
        $this->assertSame(1, $stats['equipment_maintenance']);
        $this->assertSame(1, $stats['classes_no_trainer']);
        $this->assertSame(1, $stats['classes_at_capacity']);
    }

    public function testGetAttentionItemsIncludesExpectedAlerts(): void
    {
        $memberId = $this->insertUser('Member', 'member', 'member@test.com', 'member', true, date('Y-m-d H:i:s'));
        $bannedId = $this->insertUser('Banned', 'banned', 'banned@test.com', 'member', false, date('Y-m-d H:i:s'));

        $classId = $this->insertClass(null);
        $sessionDate = date('Y-m-d H:i:s', strtotime('+1 minute'));
        $sessionId = $this->insertSession($classId, $sessionDate, 10);
        $this->insertEnrollment($memberId, $sessionId);

        $equipmentId = $this->insertEquipment('Rowing');
        $this->insertUnit($equipmentId, 'maintenance');

        $this->insertSubscription($memberId, 'active', date('Y-m-d', strtotime('-30 days')), date('Y-m-d', strtotime('-1 day')));

        $items = AdminLog::getAttentionItems($this->db);
        $hrefs = array_column($items, 'href');

        $this->assertContains('/src/pages/admin-classes.php?filter=no_trainer', $hrefs);
        $this->assertContains('/src/pages/equipment-map.php', $hrefs);
        $this->assertContains('/src/pages/admin-users.php?role=member&status=banned', $hrefs);
        $this->assertContains('/src/pages/admin-users.php?role=member&status=all&subscription=expired', $hrefs);

        $this->assertNotContains('/src/pages/admin-classes.php?filter=at_capacity', $hrefs);
        $this->assertNotContains('/src/pages/admin-classes.php?filter=empty', $hrefs);
    }
}
