<?php
declare(strict_types=1);

class Announcement
{
    public static function getAll(PDO $db, int $limit = 20, int $offset = 0): array
    {
        $stmt = $db->prepare(
            "SELECT a.id, a.title, a.body, a.pinned, a.type, a.read_time, a.image, a.created_at,
                    u.name AS author_name
             FROM announcement a
             JOIN user u ON u.user_id = a.author_id
             ORDER BY a.pinned DESC, a.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getById(PDO $db, int $id): ?array
    {
        $stmt = $db->prepare(
            "SELECT a.id, a.title, a.body, a.pinned, a.type, a.read_time, a.image, a.created_at,
                    u.name AS author_name
             FROM announcement a
             JOIN user u ON u.user_id = a.author_id
             WHERE a.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function countAll(PDO $db): int
    {
        return (int) $db->query("SELECT COUNT(*) FROM announcement")->fetchColumn();
    }

    public static function create(PDO $db, int $authorId, string $title, string $body, bool $pinned, string $type, int $readTime, ?string $image = null): void
    {
        $stmt = $db->prepare(
            "INSERT INTO announcement (title, body, author_id, pinned, type, read_time, image)
             VALUES (:title, :body, :author_id, :pinned, :type, :read_time, :image)"
        );
        $stmt->execute([
            ':title'     => $title,
            ':body'      => $body,
            ':author_id' => $authorId,
            ':pinned'    => $pinned ? 1 : 0,
            ':type'      => $type,
            ':read_time' => $readTime,
            ':image'     => $image,
        ]);
    }

    public static function update(PDO $db, int $id, string $title, string $body, string $type, int $readTime, bool $pinned, ?string $image = null): void
    {
        if ($image !== null) {
            $stmt = $db->prepare(
                "UPDATE announcement SET title=:title, body=:body, type=:type, read_time=:read_time, pinned=:pinned, image=:image WHERE id=:id"
            );
            $stmt->execute([':title' => $title, ':body' => $body, ':type' => $type, ':read_time' => $readTime, ':pinned' => $pinned ? 1 : 0, ':image' => $image, ':id' => $id]);
        } else {
            $stmt = $db->prepare(
                "UPDATE announcement SET title=:title, body=:body, type=:type, read_time=:read_time, pinned=:pinned WHERE id=:id"
            );
            $stmt->execute([':title' => $title, ':body' => $body, ':type' => $type, ':read_time' => $readTime, ':pinned' => $pinned ? 1 : 0, ':id' => $id]);
        }
    }

    public static function delete(PDO $db, int $id): void
    {
        $db->prepare("DELETE FROM announcement WHERE id = :id")->execute([':id' => $id]);
    }

    public static function togglePin(PDO $db, int $id): bool
    {
        $db->prepare("UPDATE announcement SET pinned = 1 - pinned WHERE id = :id")->execute([':id' => $id]);
        $row = $db->prepare("SELECT pinned FROM announcement WHERE id = :id");
        $row->execute([':id' => $id]);
        return (bool) $row->fetchColumn();
    }
}
