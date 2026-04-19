<?php
declare(strict_types = 1);

class Session {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    public function getId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public function getName(): ?string {
        return $_SESSION['user_name'] ?? null;
    }

    public function getRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    public function setUser(int $id, string $name, string $role): void {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = $role;
    }

    public function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function verifyCsrfToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function logout(): void {
        session_destroy();
    }

    public function setRegisterError(string $msg, array $formData = []): void {
        $_SESSION['register_error']     = $msg;
        $_SESSION['register_form_data'] = $formData;
    }

    public function popRegisterError(): ?string {
        $msg = $_SESSION['register_error'] ?? null;
        unset($_SESSION['register_error']);
        return $msg;
    }

    public function popRegisterFormData(): array {
        $data = $_SESSION['register_form_data'] ?? [];
        unset($_SESSION['register_form_data']);
        return $data;
    }

    public function addMessage(string $type, string $text): void {
        $_SESSION['messages'][] = ['type' => $type, 'text' => $text];
    }

    public function getMessages(): array {
        $msgs = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);
        return $msgs;
    }
}
