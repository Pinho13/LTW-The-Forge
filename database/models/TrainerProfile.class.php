<?php
declare(strict_types=1);

class TrainerProfile
{
    public static function getAllWithUser(PDO $db): array
    {
        $stmt = $db->prepare(
            "SELECT u.user_id, u.name, u.username,
                    tp.bio, tp.specializations, tp.certifications, tp.is_featured
             FROM user u
             JOIN trainer_profile tp ON tp.user_id = u.user_id
             WHERE u.is_active = 1
             ORDER BY u.name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getByUserId(PDO $db, int $userId): ?array
    {
        $stmt = $db->prepare(
            "SELECT u.user_id, u.name, u.username,
                    tp.bio, tp.specializations, tp.certifications, tp.is_featured
             FROM user u
             JOIN trainer_profile tp ON tp.user_id = u.user_id
             WHERE u.user_id = :id AND u.is_active = 1"
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getUpcomingClasses(PDO $db, int $trainerId, int $limit = 5): array
    {
        $stmt = $db->prepare(
            "SELECT cs.id AS session_id, c.name AS class_name, ct.name AS type_name,
                    cs.datetime, cs.room, cs.capacity,
                    COUNT(CASE WHEN e.status = 'enrolled' THEN 1 END) AS enrolled_count
             FROM class c
             JOIN class_session cs ON cs.class_id = c.id
             LEFT JOIN class_type ct ON ct.id = c.type_id
             LEFT JOIN enrollment e ON e.session_id = cs.id
             WHERE c.trainer_id = :trainer_id
               AND cs.datetime > datetime('now', 'localtime')
             GROUP BY cs.id
             ORDER BY cs.datetime ASC
             LIMIT :limit"
        );
        $stmt->bindValue(':trainer_id', $trainerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function upsert(PDO $db, int $userId, string $bio, string $specializations, string $certifications): void
    {
        $stmt = $db->prepare(
            "INSERT INTO trainer_profile (user_id, bio, specializations, certifications)
             VALUES (:uid, :bio, :spec, :cert)
             ON CONFLICT (user_id) DO UPDATE SET
               bio = excluded.bio,
               specializations = excluded.specializations,
               certifications = excluded.certifications"
        );
        $stmt->execute([
            ':uid'  => $userId,
            ':bio'  => $bio,
            ':spec' => $specializations,
            ':cert' => $certifications,
        ]);
    }
}
