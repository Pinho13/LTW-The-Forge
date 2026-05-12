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
        public readonly ?string $phone = null,
        public readonly ?string $profile_photo = null,
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

    public static function findByUsername(PDO $db, string $username): ?self
    {
        $stmt = $db->prepare('SELECT * FROM user WHERE username = ?');
        $stmt->execute([$username]);

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

    public static function phoneHasAreaCode(string $phone): bool
    {
        return str_starts_with(ltrim($phone), '+');
    }

    public static function phoneHasValidLength(string $phone): bool
    {
        $digits = preg_replace('/\D/', '', $phone);
        $len    = strlen($digits);
        return $len >= 7 && $len <= 15;
    }

    public static function phonePassesPortugalRules(string $phone): bool
    {
        if (!str_starts_with(ltrim($phone), '+351')) return true;
        $digits = preg_replace('/\D/', '', $phone);
        return strlen(substr($digits, 3)) === 9;
    }

    public function getInitials(): string
    {
        $words = array_values(array_filter(explode(' ', $this->name)));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
        return $initials;
    }

    public static function isValidPhone(string $phone): bool
    {
        return self::phoneHasAreaCode($phone)
            && self::phoneHasValidLength($phone)
            && self::phonePassesPortugalRules($phone);
    }

    public static function verifyCurrentPassword(PDO $db, int $userId, string $password): bool
    {
        $stmt = $db->prepare('SELECT password_hash FROM user WHERE user_id = :id');
        $stmt->execute([':id' => $userId]);
        $hash = $stmt->fetchColumn();
        return $hash !== false && password_verify($password, $hash);
    }

    public static function updatePassword(PDO $db, int $userId, string $newPassword): void
    {
        $stmt = $db->prepare('UPDATE user SET password_hash = :hash WHERE user_id = :id');
        $stmt->execute([':hash' => password_hash($newPassword, PASSWORD_BCRYPT), ':id' => $userId]);
    }

    public static function delete(PDO $db, int $userId): void
    {
        $stmt = $db->prepare('DELETE FROM user WHERE user_id = :id');
        $stmt->execute([':id' => $userId]);
    }

    public static function update(PDO $db, int $userId, string $name, string $username, string $email, ?string $phone): void
    {
        $stmt = $db->prepare(
            'UPDATE user SET name = :name, username = :username, email = :email, phone = :phone
             WHERE user_id = :id'
        );
        $stmt->execute([':name' => $name, ':username' => $username, ':email' => $email, ':phone' => $phone, ':id' => $userId]);
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
            !empty($row['phone']) ? (string) $row['phone'] : null,
            !empty($row['profile_photo']) ? (string) $row['profile_photo'] : null,
        );
    }
}