<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/AdminUser.class.php';

class AdminUserTest extends TestCase
{
    private PDO $db;
    private int $adminId;
    private int $memberId;
    private int $trainerId;
    private int $expiredMemberId;
    private int $basicPlanId;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->adminId = $this->insertUser('Admin', 'admin', 'admin@test.com', 'admin', true, '-1 day');
        $this->memberId = $this->insertUser('Member', 'member', 'member@test.com', 'member', true, '-2 days');
        $this->trainerId = $this->insertUser('Trainer', 'trainer', 'trainer@test.com', 'trainer', true, '-3 days');
        $this->expiredMemberId = $this->insertUser('Expired', 'expired', 'expired@test.com', 'member', true, '-10 days');

        $this->basicPlanId = $this->insertPlan('Basic');
        $this->insertSubscription($this->memberId, $this->basicPlanId, 'active', '2025-01-01', '2025-02-01');
        $this->insertSubscription($this->expiredMemberId, $this->basicPlanId, 'active', '2025-01-01', '2025-01-15');
    }

    private function insertUser(string $name, string $username, string $email, string $role, bool $active, string $createdModifier): int
    {
        $createdAt = $this->db->query("SELECT datetime('now','localtime','{$createdModifier}')")->fetchColumn();
        $stmt = $this->db->prepare(
            "INSERT INTO user (name, username, email, password_hash, role, is_active, created_at)
             VALUES (:name, :username, :email, 'hash', :role, :active, :created_at)"
        );
        $stmt->execute([
            ':name' => $name,
            ':username' => $username,
            ':email' => $email,
            ':role' => $role,
            ':active' => $active ? 1 : 0,
            ':created_at' => $createdAt,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function insertPlan(string $name): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO membership_plan (name, price, description, max_classes_per_month)
             VALUES (:name, 10.0, '', 10)"
        );
        $stmt->execute([':name' => $name]);
        return (int) $this->db->lastInsertId();
    }

    private function insertSubscription(int $memberId, int $planId, string $status, string $startDate, string $endDate): void
    {
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

    public function testGetAllFiltersByRole(): void
    {
        $rows = AdminUser::getAll($this->db, 'trainer');
        $this->assertCount(1, $rows);
        $this->assertSame($this->trainerId, (int) $rows[0]['user_id']);
    }

    public function testGetAllFiltersBySearch(): void
    {
        $rows = AdminUser::getAll($this->db, '', 'member');
        $this->assertCount(1, $rows);
        $this->assertSame($this->memberId, (int) $rows[0]['user_id']);

        $rows = AdminUser::getAll($this->db, '', 'expired@test.com');
        $this->assertCount(1, $rows);
        $this->assertSame($this->expiredMemberId, (int) $rows[0]['user_id']);
    }

    public function testGetAllFiltersByStatus(): void
    {
        AdminUser::setActive($this->db, $this->memberId, false);

        $active = AdminUser::getAll($this->db, '', '', 'active');
        $ids = array_column($active, 'user_id');
        $this->assertNotContains($this->memberId, $ids);

        $banned = AdminUser::getAll($this->db, '', '', 'banned');
        $this->assertCount(1, $banned);
        $this->assertSame($this->memberId, (int) $banned[0]['user_id']);
    }

    public function testGetAllFiltersByJoinedWeek(): void
    {
        $rows = AdminUser::getAll($this->db, '', '', 'all', 0, 'week');
        $ids = array_column($rows, 'user_id');
        $this->assertContains($this->adminId, $ids);
        $this->assertContains($this->memberId, $ids);
        $this->assertContains($this->trainerId, $ids);
        $this->assertNotContains($this->expiredMemberId, $ids);
    }

    public function testGetAllFiltersBySubscriptionExpired(): void
    {
        $rows = AdminUser::getAll($this->db, 'member', '', 'all', 0, '', 'expired');
        $this->assertCount(1, $rows);
        $this->assertSame($this->expiredMemberId, (int) $rows[0]['user_id']);
    }

    public function testUpdateDetailsPersists(): void
    {
        AdminUser::updateDetails($this->db, $this->memberId, 'New Name', 'new@test.com', '123');
        $row = AdminUser::getById($this->db, $this->memberId);

        $this->assertSame('New Name', $row['name']);
        $this->assertSame('new@test.com', $row['email']);
        $this->assertSame('123', $row['phone']);
    }

    public function testSetRolePersists(): void
    {
        AdminUser::setRole($this->db, $this->memberId, 'trainer');
        $row = AdminUser::getById($this->db, $this->memberId);

        $this->assertSame('trainer', $row['role']);
    }

    public function testSetActivePersists(): void
    {
        AdminUser::setActive($this->db, $this->memberId, false);
        $row = AdminUser::getById($this->db, $this->memberId);

        $this->assertSame(0, (int) $row['is_active']);
    }

    public function testEmailAndUsernameExists(): void
    {
        $this->assertTrue(AdminUser::emailExists($this->db, 'member@test.com', $this->adminId));
        $this->assertTrue(AdminUser::usernameExists($this->db, 'member', $this->adminId));
        $this->assertFalse(AdminUser::emailExists($this->db, 'missing@test.com', $this->adminId));
        $this->assertFalse(AdminUser::usernameExists($this->db, 'missing', $this->adminId));
    }
}
