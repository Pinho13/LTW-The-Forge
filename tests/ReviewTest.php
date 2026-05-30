<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/Review.class.php';

class ReviewTest extends TestCase
{
    private PDO $db;
    private int $memberId;
    private int $classId;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->memberId = $this->insertUser('Member One', 'member1', 'member1@test.com');
        $typeId = $this->insertClassType('Yoga');
        $this->classId = $this->insertClass('Morning Yoga', $typeId);
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

    private function insertClassType(string $name): int
    {
        $stmt = $this->db->prepare("INSERT INTO class_type (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        return (int) $this->db->lastInsertId();
    }

    private function insertClass(string $name, int $typeId): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO class (name, type_id, description, duration_minutes, intensity)
             VALUES (:name, :type_id, '', 60, 3)"
        );
        $stmt->execute([':name' => $name, ':type_id' => $typeId]);
        return (int) $this->db->lastInsertId();
    }

    public function testFindByMemberAndClassReturnsNullWhenMissing(): void
    {
        $this->assertNull(Review::findByMemberAndClass($this->db, $this->memberId, $this->classId));
    }

    public function testUpsertCreatesAndUpdatesReview(): void
    {
        Review::upsert($this->db, $this->memberId, $this->classId, 5, 'Great class');
        $first = Review::findByMemberAndClass($this->db, $this->memberId, $this->classId);

        $this->assertNotNull($first);
        $this->assertSame(5, (int) $first['rating']);
        $this->assertSame('Great class', $first['comment']);

        Review::upsert($this->db, $this->memberId, $this->classId, 3, 'Okay');
        $second = Review::findByMemberAndClass($this->db, $this->memberId, $this->classId);

        $this->assertSame(3, (int) $second['rating']);
        $this->assertSame('Okay', $second['comment']);

        $count = (int) $this->db->query("SELECT COUNT(*) FROM review")->fetchColumn();
        $this->assertSame(1, $count);
    }
}
