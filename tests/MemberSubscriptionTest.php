<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/MemberSubscription.class.php';

class MemberSubscriptionTest extends TestCase
{
    private PDO $db;
    private int $memberId;
    private int $basicPlanId;
    private int $premiumPlanId;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->memberId = $this->insertUser('Member One', 'member1', 'member1@test.com');
        $this->basicPlanId = $this->insertPlan('Basic', 15.0);
        $this->premiumPlanId = $this->insertPlan('Premium', 25.0);
    }

    private function insertUser(string $name, string $username, string $email): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO user (name, username, email, password_hash, role)
             VALUES (:name, :username, :email, 'hash', 'member')"
        );
        $stmt->execute([':name' => $name, ':username' => $username, ':email' => $email]);
        return (int) $this->db->lastInsertId();
    }

    private function insertPlan(string $name, float $price): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO membership_plan (name, price, description, max_classes_per_month)
             VALUES (:name, :price, '', 10)"
        );
        $stmt->execute([':name' => $name, ':price' => $price]);
        return (int) $this->db->lastInsertId();
    }

    private function insertSubscription(int $planId, string $status, string $startDate, string $endDate): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO member_subscription (member_id, plan_id, start_date, end_date, status)
             VALUES (:member_id, :plan_id, :start_date, :end_date, :status)"
        );
        $stmt->execute([
            ':member_id' => $this->memberId,
            ':plan_id' => $planId,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':status' => $status,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function testPauseFreezesLatestActiveSubscription(): void
    {
        $startDate = date('Y-m-d', strtotime('-5 days'));
        $endDate = date('Y-m-d', strtotime('+25 days'));
        $this->insertSubscription($this->basicPlanId, 'active', $startDate, $endDate);

        MemberSubscription::pause($this->db, $this->memberId, 15);

        $row = $this->db->query("SELECT status, frozen_until FROM member_subscription ORDER BY start_date DESC LIMIT 1")
            ->fetch();

        $this->assertSame('frozen', $row['status']);
        $this->assertSame(date('Y-m-d', strtotime('+15 days')), $row['frozen_until']);
    }

    public function testGetActivePlanNamePrefersFrozen(): void
    {
        $this->insertSubscription($this->basicPlanId, 'active', '2025-01-01', '2025-02-01');
        $this->insertSubscription($this->premiumPlanId, 'frozen', '2025-02-01', '2025-03-01');

        $this->assertSame('Premium', MemberSubscription::getActivePlanName($this->db, $this->memberId));
    }

    public function testGetActivePlanNameFallsBackToActive(): void
    {
        $this->insertSubscription($this->basicPlanId, 'active', '2025-01-01', '2025-02-01');

        $this->assertSame('Basic', MemberSubscription::getActivePlanName($this->db, $this->memberId));
    }

    public function testGetActivePlanNameReturnsNullWhenMissing(): void
    {
        $this->assertNull(MemberSubscription::getActivePlanName($this->db, $this->memberId));
    }
}
