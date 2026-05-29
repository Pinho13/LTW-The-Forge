<?php
declare(strict_types=1);

class ClassCatalog
{
    public static function getAllClasses(PDO $db): array
    {
        return $db->query(
            "SELECT c.id, c.name, c.type_id, c.description, c.duration_minutes, c.intensity, c.trainer_id,
                    ct.name AS type_name, u.name AS trainer_name
             FROM class c
             LEFT JOIN class_type ct ON ct.id = c.type_id
             LEFT JOIN user u ON u.user_id = c.trainer_id
             ORDER BY c.name ASC"
        )->fetchAll();
    }

    public static function getAllTypes(PDO $db): array
    {
        return $db->query("SELECT id, name FROM class_type ORDER BY name ASC")->fetchAll();
    }

    public static function getAllTrainers(PDO $db): array
    {
        return $db->query(
            "SELECT user_id AS id, name FROM user WHERE role = 'trainer' ORDER BY name ASC"
        )->fetchAll();
    }

    public static function updateSession(PDO $db, int $sessionId, string $datetime, string $room, int $capacity): void
    {
        $db->prepare(
            "UPDATE class_session SET datetime=:datetime, room=:room, capacity=:capacity WHERE id=:id"
        )->execute([':datetime' => $datetime, ':room' => $room, ':capacity' => $capacity, ':id' => $sessionId]);
    }

    public static function updateClass(PDO $db, int $classId, string $name, int $typeId, string $description, int $duration, int $intensity, ?int $trainerId): void
    {
        $db->prepare(
            "UPDATE class SET name=:name, type_id=:type_id, description=:description,
             duration_minutes=:duration, intensity=:intensity, trainer_id=:trainer_id WHERE id=:id"
        )->execute([
            ':name'        => $name,
            ':type_id'     => $typeId,
            ':description' => $description,
            ':duration'    => $duration,
            ':intensity'   => $intensity,
            ':trainer_id'  => $trainerId,
            ':id'          => $classId,
        ]);
    }

    public static function createSession(PDO $db, int $classId, string $datetime, string $room, int $capacity): int
    {
        $db->prepare(
            "INSERT INTO class_session (class_id, datetime, room, capacity) VALUES (:class_id,:datetime,:room,:capacity)"
        )->execute([':class_id' => $classId, ':datetime' => $datetime, ':room' => $room, ':capacity' => $capacity]);
        return (int) $db->lastInsertId();
    }

    public static function createClass(PDO $db, string $name, int $typeId, string $description, int $duration, int $intensity, ?int $trainerId): int
    {
        $db->prepare(
            "INSERT INTO class (name, type_id, description, duration_minutes, intensity, trainer_id)
             VALUES (:name,:type_id,:description,:duration,:intensity,:trainer_id)"
        )->execute([
            ':name'        => $name,
            ':type_id'     => $typeId,
            ':description' => $description,
            ':duration'    => $duration,
            ':intensity'   => $intensity,
            ':trainer_id'  => $trainerId,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function deleteSession(PDO $db, int $sessionId): void
    {
        $db->prepare("DELETE FROM class_session WHERE id=:id")->execute([':id' => $sessionId]);
    }
}
