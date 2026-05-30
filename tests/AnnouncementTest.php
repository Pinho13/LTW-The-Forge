<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/Announcement.class.php';

class AnnouncementTest extends TestCase
{
    private PDO $db;
    private int $authorId;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->db->exec("
            INSERT INTO user (name, username, email, password_hash, role) VALUES
                ('Admin User', 'admin', 'admin@test.com', 'hash', 'admin')
        ");
        $this->authorId = (int) $this->db->lastInsertId();
    }

    public function testCreateAndGetById(): void
    {
        Announcement::create($this->db, $this->authorId, 'Title', 'Body', true, 'Update', 3, null);
        $id = (int) $this->db->lastInsertId();

        $announcement = Announcement::getById($this->db, $id);

        $this->assertNotNull($announcement);
        $this->assertSame('Title', $announcement['title']);
        $this->assertSame('Body', $announcement['body']);
        $this->assertSame(1, (int) $announcement['pinned']);
        $this->assertSame('Update', $announcement['type']);
        $this->assertSame(3, (int) $announcement['read_time']);
        $this->assertSame('Admin User', $announcement['author_name']);
    }

    public function testCountAll(): void
    {
        $this->assertSame(0, Announcement::countAll($this->db));

        Announcement::create($this->db, $this->authorId, 'First', 'Body', false, 'Gym News', 2, null);
        Announcement::create($this->db, $this->authorId, 'Second', 'Body', false, 'Gym News', 2, null);

        $this->assertSame(2, Announcement::countAll($this->db));
    }

    public function testTogglePinFlipsState(): void
    {
        Announcement::create($this->db, $this->authorId, 'Title', 'Body', false, 'Gym News', 2, null);
        $id = (int) $this->db->lastInsertId();

        $this->assertTrue(Announcement::togglePin($this->db, $id));
        $this->assertFalse(Announcement::togglePin($this->db, $id));
    }

    public function testUpdateWithoutImageKeepsExistingImage(): void
    {
        Announcement::create($this->db, $this->authorId, 'Title', 'Body', false, 'Gym News', 2, 'old.jpg');
        $id = (int) $this->db->lastInsertId();

        Announcement::update($this->db, $id, 'New', 'New Body', 'Update', 4, true, null);
        $announcement = Announcement::getById($this->db, $id);

        $this->assertSame('old.jpg', $announcement['image']);
        $this->assertSame('New', $announcement['title']);
        $this->assertSame('New Body', $announcement['body']);
        $this->assertSame(1, (int) $announcement['pinned']);
        $this->assertSame('Update', $announcement['type']);
        $this->assertSame(4, (int) $announcement['read_time']);
    }

    public function testUpdateWithImageOverridesExistingImage(): void
    {
        Announcement::create($this->db, $this->authorId, 'Title', 'Body', false, 'Gym News', 2, 'old.jpg');
        $id = (int) $this->db->lastInsertId();

        Announcement::update($this->db, $id, 'New', 'New Body', 'Update', 4, true, 'new.jpg');
        $announcement = Announcement::getById($this->db, $id);

        $this->assertSame('new.jpg', $announcement['image']);
    }

    public function testDeleteRemovesAnnouncement(): void
    {
        Announcement::create($this->db, $this->authorId, 'Title', 'Body', false, 'Gym News', 2, null);
        $id = (int) $this->db->lastInsertId();

        Announcement::delete($this->db, $id);

        $this->assertNull(Announcement::getById($this->db, $id));
        $this->assertSame(0, Announcement::countAll($this->db));
    }
}
