<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/TrainerProfile.class.php';

class TrainerProfileTest extends TestCase
{
    private PDO $db;
    private int $trainerId;
    private int $inactiveTrainerId;
    private int $classId;
    private int $sessionId;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->trainerId = $this->insertUser('Trainer One', 'trainer1', 'trainer1@test.com', true);
        $this->inactiveTrainerId = $this->insertUser('Trainer Two', 'trainer2', 'trainer2@test.com', false);

        $this->db->exec("INSERT INTO class_type (name) VALUES ('Yoga');");
        $typeId = (int) $this->db->lastInsertId();
        $this->classId = $this->insertClass('Morning Yoga', $typeId, $this->trainerId);
        $this->sessionId = $this->insertSession($this->classId, date('Y-m-d H:i:s', strtotime('+1 day')), 'Studio 1', 10);
    }

    private function insertUser(string $name, string $username, string $email, bool $isActive): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO user (name, username, email, password_hash, role, is_active)
             VALUES (:name, :username, :email, 'hash', 'trainer', :is_active)"
        );
        $stmt->execute([
            ':name' => $name,
            ':username' => $username,
            ':email' => $email,
            ':is_active' => $isActive ? 1 : 0,
        ]);
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

    private function enroll(int $memberId, int $sessionId, string $status = 'enrolled'): void
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

    public function testGetAllWithUserReturnsOnlyActive(): void
    {
        TrainerProfile::upsert($this->db, $this->trainerId, 'Bio', 'Spec', 'Cert');
        TrainerProfile::upsert($this->db, $this->inactiveTrainerId, 'Bio', 'Spec', 'Cert');

        $rows = TrainerProfile::getAllWithUser($this->db);

        $this->assertCount(1, $rows);
        $this->assertSame($this->trainerId, (int) $rows[0]['user_id']);
    }

    public function testGetByUserIdReturnsNullForInactive(): void
    {
        TrainerProfile::upsert($this->db, $this->inactiveTrainerId, 'Bio', 'Spec', 'Cert');

        $this->assertNull(TrainerProfile::getByUserId($this->db, $this->inactiveTrainerId));
    }

    public function testGetByUserIdReturnsProfile(): void
    {
        TrainerProfile::upsert($this->db, $this->trainerId, 'Bio', 'Spec', 'Cert');

        $row = TrainerProfile::getByUserId($this->db, $this->trainerId);

        $this->assertNotNull($row);
        $this->assertSame('Bio', $row['bio']);
        $this->assertSame('Spec', $row['specializations']);
        $this->assertSame('Cert', $row['certifications']);
    }

    public function testGetUpcomingClassesCountsEnrolledOnly(): void
    {
        $memberId = $this->insertUser('Member', 'member1', 'member1@test.com', true);
        $this->enroll($memberId, $this->sessionId, 'enrolled');
        $this->enroll($memberId, $this->sessionId, 'cancelled');

        $rows = TrainerProfile::getUpcomingClasses($this->db, $this->trainerId, 5);

        $this->assertCount(1, $rows);
        $this->assertSame($this->sessionId, (int) $rows[0]['session_id']);
        $this->assertSame(1, (int) $rows[0]['enrolled_count']);
    }

    public function testUpsertUpdatesExistingProfile(): void
    {
        TrainerProfile::upsert($this->db, $this->trainerId, 'Bio', 'Spec', 'Cert');
        TrainerProfile::upsert($this->db, $this->trainerId, 'Bio 2', 'Spec 2', 'Cert 2');

        $row = TrainerProfile::getByUserId($this->db, $this->trainerId);

        $this->assertSame('Bio 2', $row['bio']);
        $this->assertSame('Spec 2', $row['specializations']);
        $this->assertSame('Cert 2', $row['certifications']);
    }
}
