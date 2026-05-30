<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/ClassCatalog.class.php';

class ClassCatalogTest extends TestCase
{
    private PDO $db;
    private int $trainerId;
    private int $yogaTypeId;
    private int $boxingTypeId;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->trainerId = $this->insertUser('Trainer One', 'trainer1', 'trainer1@test.com', 'trainer');
        $this->yogaTypeId = $this->insertClassType('Yoga');
        $this->boxingTypeId = $this->insertClassType('Boxing');
    }

    private function insertUser(string $name, string $username, string $email, string $role): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO user (name, username, email, password_hash, role)
             VALUES (:name, :username, :email, 'hash', :role)"
        );
        $stmt->execute([':name' => $name, ':username' => $username, ':email' => $email, ':role' => $role]);
        return (int) $this->db->lastInsertId();
    }

    private function insertClassType(string $name): int
    {
        $stmt = $this->db->prepare("INSERT INTO class_type (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        return (int) $this->db->lastInsertId();
    }

    private function createClass(string $name, int $typeId, int $duration, int $intensity, ?int $trainerId, int $isFeatured = 0): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO class (name, type_id, description, duration_minutes, intensity, trainer_id, is_featured)
             VALUES (:name, :type_id, '', :duration, :intensity, :trainer_id, :is_featured)"
        );
        $stmt->execute([
            ':name' => $name,
            ':type_id' => $typeId,
            ':duration' => $duration,
            ':intensity' => $intensity,
            ':trainer_id' => $trainerId,
            ':is_featured' => $isFeatured,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function createSession(int $classId, string $datetime, string $room, int $capacity): int
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

    public function testGetAllClassesReturnsTypeAndTrainer(): void
    {
        $classId = $this->createClass('Morning Yoga', $this->yogaTypeId, 60, 2, $this->trainerId);

        $rows = ClassCatalog::getAllClasses($this->db);
        $this->assertCount(1, $rows);
        $this->assertSame($classId, (int) $rows[0]['id']);
        $this->assertSame('Yoga', $rows[0]['type_name']);
        $this->assertSame('Trainer One', $rows[0]['trainer_name']);
    }

    public function testGetFeaturedReturnsOnlyFeatured(): void
    {
        $featuredId = $this->createClass('Featured', $this->yogaTypeId, 45, 3, $this->trainerId, 1);
        $this->createClass('Normal', $this->boxingTypeId, 45, 3, $this->trainerId, 0);

        $rows = ClassCatalog::getFeatured($this->db);
        $this->assertCount(1, $rows);
        $this->assertSame($featuredId, (int) $rows[0]['id']);
    }

    public function testGetFeaturedIncludesNextRoom(): void
    {
        $classId = $this->createClass('Featured', $this->yogaTypeId, 45, 3, $this->trainerId, 1);
        $future = date('Y-m-d H:i:s', strtotime('+1 day'));
        $this->createSession($classId, $future, 'Studio 1', 10);

        $rows = ClassCatalog::getFeatured($this->db);
        $this->assertSame('Studio 1', $rows[0]['next_room']);
    }

    public function testGetAllTypesReturnsSortedTypes(): void
    {
        $rows = ClassCatalog::getAllTypes($this->db);
        $this->assertSame(['Boxing', 'Yoga'], array_column($rows, 'name'));
    }

    public function testGetAllTrainersReturnsOnlyTrainers(): void
    {
        $this->insertUser('Member', 'member', 'member@test.com', 'member');

        $rows = ClassCatalog::getAllTrainers($this->db);
        $this->assertCount(1, $rows);
        $this->assertSame('Trainer One', $rows[0]['name']);
    }

    public function testUpdateClassPersistsChanges(): void
    {
        $classId = $this->createClass('Old Name', $this->yogaTypeId, 60, 2, $this->trainerId);

        ClassCatalog::updateClass($this->db, $classId, 'New Name', $this->boxingTypeId, 'desc', 45, 4, null);

        $row = $this->db->query("SELECT name, type_id, duration_minutes, intensity, trainer_id FROM class WHERE id = {$classId}")
            ->fetch();
        $this->assertSame('New Name', $row['name']);
        $this->assertSame($this->boxingTypeId, (int) $row['type_id']);
        $this->assertSame(45, (int) $row['duration_minutes']);
        $this->assertSame(4, (int) $row['intensity']);
        $this->assertNull($row['trainer_id']);
    }

    public function testUpdateSessionPersistsChanges(): void
    {
        $classId = $this->createClass('Class', $this->yogaTypeId, 60, 2, $this->trainerId);
        $sessionId = $this->createSession($classId, '2025-01-01 10:00:00', 'Room A', 10);

        ClassCatalog::updateSession($this->db, $sessionId, '2025-01-01 11:00:00', 'Room B', 12);

        $row = $this->db->query("SELECT datetime, room, capacity FROM class_session WHERE id = {$sessionId}")
            ->fetch();
        $this->assertSame('2025-01-01 11:00:00', $row['datetime']);
        $this->assertSame('Room B', $row['room']);
        $this->assertSame(12, (int) $row['capacity']);
    }

    public function testCreateAndDeleteSession(): void
    {
        $classId = $this->createClass('Class', $this->yogaTypeId, 60, 2, $this->trainerId);

        $sessionId = ClassCatalog::createSession($this->db, $classId, '2025-01-01 10:00:00', 'Room A', 10);
        $countBefore = (int) $this->db->query("SELECT COUNT(*) FROM class_session")->fetchColumn();

        ClassCatalog::deleteSession($this->db, $sessionId);
        $countAfter = (int) $this->db->query("SELECT COUNT(*) FROM class_session")->fetchColumn();

        $this->assertSame(1, $countBefore);
        $this->assertSame(0, $countAfter);
    }

    public function testCreateClassReturnsId(): void
    {
        $classId = ClassCatalog::createClass($this->db, 'New Class', $this->yogaTypeId, 'desc', 50, 3, $this->trainerId);
        $this->assertGreaterThan(0, $classId);
    }
}
