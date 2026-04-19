<?php
declare(strict_types = 1);

class User {
    public function __construct(
        public readonly int    $user_id,
        public readonly string $name,
        public readonly string $username,
        public readonly string $email,
        public readonly string $role,
        public readonly bool   $is_active,
        public readonly string $created_at,
    ) {}

    public static function findByEmail(PDO $db, string $email): ?self {
        $stmt = $db->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if (!$row) return null;
        return self::fromRow($row);
    }

    public static function register(
        PDO    $db,
        string $name,
        string $username,
        string $email,
        string $password,
        string $role = 'member'
    ): self {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare(
            'INSERT INTO user (name, username, email, password_hash, role)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $username, $email, $hash, $role]);
        $id = (int) $db->lastInsertId();
        return new self($id, $name, $username, $email, $role, true, date('Y-m-d H:i:s'));
    }

    public static function verifyPassword(PDO $db, string $email, string $password): ?self {
        $stmt = $db->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($password, $row['password_hash'])) return null;
        return self::fromRow($row);
    }

    private static function fromRow(array $row): self {
        return new self(
            $row['user_id'],
            $row['name'],
            $row['username'],
            $row['email'],
            $row['role'],
            (bool) $row['is_active'],
            $row['created_at'],
        );
    }
}
