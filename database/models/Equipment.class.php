<?php
declare(strict_types=1);

class Equipment
{
    public static function getCatalog(PDO $db): array
    {
        $stmt = $db->prepare("SELECT id, name, type, description, photo, default_w, default_h FROM equipment ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllWithUnits(PDO $db): array
    {
        $stmt = $db->prepare(
            "SELECT e.id, e.name, e.type, e.description,
                    COUNT(eu.id) AS total_units,
                    COUNT(CASE WHEN eu.status = 'available'
                                AND NOT EXISTS (
                                    SELECT 1 FROM equipment_reservation er
                                    WHERE er.unit_id = eu.id
                                      AND er.start_datetime <= datetime('now', '+1 hour')
                                      AND er.end_datetime   >  datetime('now', '+1 hour')
                                ) THEN 1 END) AS available_units
             FROM equipment e
             LEFT JOIN equipment_unit eu ON eu.equipment_id = e.id
             GROUP BY e.id
             ORDER BY e.name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllUnitsWithStatus(PDO $db): array
    {
        $stmt = $db->prepare(
            "SELECT eu.id, eu.equipment_id, eu.identifier, eu.status,
                    eu.map_x, eu.map_y, eu.map_w, eu.map_h, eu.rotation,
                    e.name AS equipment_name, e.photo,
                    CASE WHEN eu.status != 'available' THEN 0
                         WHEN EXISTS (
                             SELECT 1 FROM equipment_reservation er
                             WHERE er.unit_id = eu.id
                               AND er.start_datetime <= datetime('now', '+1 hour')
                               AND er.end_datetime   >  datetime('now', '+1 hour')
                         ) THEN 0
                         ELSE 1 END AS is_available
             FROM equipment_unit eu
             JOIN equipment e ON e.id = eu.equipment_id
             ORDER BY eu.equipment_id, eu.id"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getReservationsForMember(PDO $db, int $memberId): array
    {
        $stmt = $db->prepare(
            "SELECT er.id, er.start_datetime, er.end_datetime,
                    e.name AS equipment_name, e.type AS equipment_type,
                    eu.identifier
             FROM equipment_reservation er
             JOIN equipment_unit eu ON eu.id = er.unit_id
             JOIN equipment e ON e.id = eu.equipment_id
             WHERE er.member_id = :mid
               AND er.end_datetime > datetime('now', '+1 hour')
             ORDER BY er.start_datetime ASC"
        );
        $stmt->execute([':mid' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function hasConflict(PDO $db, int $unitId, string $start, string $end, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM equipment_reservation
                WHERE unit_id = :uid
                  AND start_datetime < :end
                  AND end_datetime   > :start";
        if ($excludeId !== null) {
            $sql .= " AND id != :excl";
        }
        $stmt = $db->prepare($sql);
        $params = [':uid' => $unitId, ':start' => $start, ':end' => $end];
        if ($excludeId !== null) $params[':excl'] = $excludeId;
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function reserve(PDO $db, int $memberId, int $unitId, string $start, string $end): void
    {
        $stmt = $db->prepare(
            "INSERT INTO equipment_reservation (member_id, unit_id, start_datetime, end_datetime)
             VALUES (:mid, :uid, :start, :end)"
        );
        $stmt->execute([':mid' => $memberId, ':uid' => $unitId, ':start' => $start, ':end' => $end]);
    }

    public static function setUnitStatus(PDO $db, int $unitId, string $status): void
    {
        $stmt = $db->prepare("UPDATE equipment_unit SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $unitId]);
    }

    public static function cancelReservation(PDO $db, int $reservationId, int $memberId): bool
    {
        $stmt = $db->prepare(
            "DELETE FROM equipment_reservation WHERE id = :id AND member_id = :mid"
        );
        $stmt->execute([':id' => $reservationId, ':mid' => $memberId]);
        return $stmt->rowCount() > 0;
    }
}
