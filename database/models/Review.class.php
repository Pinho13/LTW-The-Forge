<?php
declare(strict_types=1);

class Review
{
    public static function findByMemberAndClass(PDO $db, int $memberId, int $classId): ?array
    {
        $stmt = $db->prepare(
            "SELECT id, rating, comment FROM review
             WHERE member_id = :member_id AND class_id = :class_id"
        );
        $stmt->execute([':member_id' => $memberId, ':class_id' => $classId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function upsert(PDO $db, int $memberId, int $classId, int $rating, string $comment): void
    {
        $stmt = $db->prepare(
            "INSERT INTO review (member_id, class_id, rating, comment, created_at)
             VALUES (:member_id, :class_id, :rating, :comment, datetime('now', '+1 hour'))
             ON CONFLICT (member_id, class_id) DO UPDATE SET
               rating = excluded.rating,
               comment = excluded.comment,
               created_at = datetime('now', '+1 hour')"
        );
        $stmt->execute([
            ':member_id' => $memberId,
            ':class_id' => $classId,
            ':rating' => $rating,
            ':comment' => $comment,
        ]);
    }
}
