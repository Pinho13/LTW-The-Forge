<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/models/Equipment.class.php';

class EquipmentTest extends TestCase
{
    private PDO $db;
    private int $memberId;
    private int $otherMemberId;
    private int $bikeId;
    private int $treadmillId;
    private int $bikeUnitAvailableId;
    private int $bikeUnitMaintenanceId;
    private int $treadmillUnitId;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/sql/schema.sql');
        $this->db->exec($schema);

        $this->memberId = $this->insertUser('Member One', 'member1', 'member1@test.com');
        $this->otherMemberId = $this->insertUser('Member Two', 'member2', 'member2@test.com');

        $this->bikeId = $this->insertEquipment('Bike');
        $this->treadmillId = $this->insertEquipment('Treadmill');

        $this->bikeUnitAvailableId = $this->insertUnit($this->bikeId, 'B1', 'available');
        $this->bikeUnitMaintenanceId = $this->insertUnit($this->bikeId, 'B2', 'maintenance');
        $this->treadmillUnitId = $this->insertUnit($this->treadmillId, 'T1', 'available');
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

    private function insertEquipment(string $name): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment (name, type, description, photo, default_w, default_h)
             VALUES (:name, 'cardio', '', NULL, 55, 40)"
        );
        $stmt->execute([':name' => $name]);
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

    private function localTime(string $modifier = ''): string
    {
        $suffix = $modifier !== '' ? ", '{$modifier}'" : '';
        return (string) $this->db->query("SELECT datetime('now','localtime'{$suffix})")->fetchColumn();
    }

    public function testGetAllWithUnitsCountsAvailability(): void
    {
        Equipment::reserve(
            $this->db,
            $this->memberId,
            $this->treadmillUnitId,
            $this->localTime('-1 hour'),
            $this->localTime('+1 hour')
        );

        $rows = Equipment::getAllWithUnits($this->db);
        $byName = [];
        foreach ($rows as $row) {
            $byName[$row['name']] = $row;
        }

        $this->assertSame(2, (int) $byName['Bike']['total_units']);
        $this->assertSame(1, (int) $byName['Bike']['available_units']);
        $this->assertSame(1, (int) $byName['Treadmill']['total_units']);
        $this->assertSame(0, (int) $byName['Treadmill']['available_units']);
    }

    public function testGetAllUnitsWithStatusFlagsAvailability(): void
    {
        Equipment::reserve(
            $this->db,
            $this->memberId,
            $this->treadmillUnitId,
            $this->localTime('-1 hour'),
            $this->localTime('+1 hour')
        );

        $rows = Equipment::getAllUnitsWithStatus($this->db);
        $byIdentifier = [];
        foreach ($rows as $row) {
            $byIdentifier[$row['identifier']] = $row;
        }

        $this->assertSame(1, (int) $byIdentifier['B1']['is_available']);
        $this->assertSame(0, (int) $byIdentifier['B2']['is_available']);
        $this->assertSame(0, (int) $byIdentifier['T1']['is_available']);
    }

    public function testHasConflictDetectsOverlap(): void
    {
        Equipment::reserve($this->db, $this->memberId, $this->bikeUnitAvailableId, '2025-01-01 10:00:00', '2025-01-01 11:00:00');

        $this->assertTrue(
            Equipment::hasConflict($this->db, $this->bikeUnitAvailableId, '2025-01-01 10:30:00', '2025-01-01 11:30:00')
        );
        $this->assertFalse(
            Equipment::hasConflict($this->db, $this->bikeUnitAvailableId, '2025-01-01 11:00:00', '2025-01-01 12:00:00')
        );
    }

    public function testHasConflictExcludesReservationId(): void
    {
        Equipment::reserve($this->db, $this->memberId, $this->bikeUnitAvailableId, '2025-01-01 10:00:00', '2025-01-01 11:00:00');
        $reservationId = (int) $this->db->lastInsertId();

        $this->assertFalse(
            Equipment::hasConflict($this->db, $this->bikeUnitAvailableId, '2025-01-01 10:00:00', '2025-01-01 11:00:00', $reservationId)
        );
    }

    public function testReserveCreatesReservation(): void
    {
        Equipment::reserve($this->db, $this->memberId, $this->bikeUnitAvailableId, '2025-01-02 10:00:00', '2025-01-02 11:00:00');

        $count = (int) $this->db->query("SELECT COUNT(*) FROM equipment_reservation")->fetchColumn();
        $this->assertSame(1, $count);
    }

    public function testCancelReservationRemovesOnlyOwner(): void
    {
        Equipment::reserve($this->db, $this->memberId, $this->bikeUnitAvailableId, '2025-01-03 10:00:00', '2025-01-03 11:00:00');
        $reservationId = (int) $this->db->lastInsertId();

        $this->assertFalse(Equipment::cancelReservation($this->db, $reservationId, $this->otherMemberId));
        $this->assertTrue(Equipment::cancelReservation($this->db, $reservationId, $this->memberId));

        $count = (int) $this->db->query("SELECT COUNT(*) FROM equipment_reservation")->fetchColumn();
        $this->assertSame(0, $count);
    }
}
