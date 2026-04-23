<?php
declare(strict_types=1);

class User
{
    public function __construct(
        public readonly int $user_id,
        public readonly string $name,
        public readonly string $username,
        public readonly string $email,
        public readonly string $role,
        public readonly bool $is_active,
        public readonly string $created_at,
    ) {}

    public static function findById(PDO $db, int $userId): ?self
    {
        $stmt = $db->prepare('SELECT * FROM user WHERE user_id = ?');
        $stmt->execute([$userId]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    public static function findByEmail(PDO $db, string $email): ?self
    {
        $stmt = $db->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    public static function register(
        PDO $db,
        string $name,
        string $username,
        string $email,
        string $password
    ): self {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare(
            'INSERT INTO user (name, username, email, password_hash, role)
             VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $name,
            $username,
            $email,
            $hashedPassword,
            'member',
        ]);

        $userId = (int) $db->lastInsertId();

        return self::findById($db, $userId)
            ?? throw new RuntimeException('Failed to load newly created user.');
    }

    public static function verifyPassword(PDO $db, string $email, string $password): ?self
    {
        $stmt = $db->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        if (!(bool) $row['is_active']) {
            return null;
        }

        if (!password_verify($password, $row['password_hash'])) {
            return null;
        }

        return self::fromRow($row);
    }

    private static function fromRow(array $row): self
    {
        return new self(
            (int) $row['user_id'],
            (string) $row['name'],
            (string) $row['username'],
            (string) $row['email'],
            (string) $row['role'],
            (bool) $row['is_active'],
            (string) $row['created_at'],
        );
    }
}