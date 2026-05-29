<?php
declare(strict_types=1);

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'secure' => !empty($_SERVER['HTTPS']),
                'samesite' => 'Lax',
            ]);

            session_start();
        }
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function hasUser(): bool
    {
        return $this->isLoggedIn();
    }

    public function getId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public function getName(): ?string
    {
        return $_SESSION['user_name'] ?? null;
    }

    public function getRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public function hasRole(string $role): bool
    {
        return $this->isLoggedIn() && $this->getRole() === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTrainer(): bool
    {
        return $this->hasRole('trainer');
    }

    public function isMember(): bool
    {
        return $this->hasRole('member');
    }

    public function requireLogin(string $redirect = '/src/pages/index.php'): void
    {
        if (!$this->isLoggedIn()) {
            $this->addMessage('error', 'You must be logged in to access this page.');
            header("Location: $redirect");
            exit;
        }
    }

    public function requireRole(string $role, string $redirect = '/src/pages/index.php'): void
    {
        if (!$this->hasRole($role)) {
            $this->addMessage('error', 'You do not have permission to access this page.');
            header("Location: $redirect");
            exit;
        }
    }

    public function setName(string $name): void
    {
        $_SESSION['user_name'] = $name;
    }

    public function setUser(int $id, string $name, string $role, string $plan = ''): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id']   = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_plan'] = strtolower($plan);
    }

    public function getPlan(): string
    {
        return $_SESSION['user_plan'] ?? '';
    }

    public function isPremium(): bool
    {
        return $this->isMember() && $this->getPlan() === 'premium';
    }

    public function isBasic(): bool
    {
        return $this->isMember() && $this->getPlan() === 'basic';
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public function getCsrfToken(): string
    {
        return $this->generateCsrfToken();
    }

    public function verifyCsrfToken(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function setFormSuccess(string $form, string $msg): void
    {
        $_SESSION['form_success'][$form] = $msg;
    }

    public function popFormSuccess(string $form): ?string
    {
        $msg = $_SESSION['form_success'][$form] ?? null;
        unset($_SESSION['form_success'][$form]);
        return $msg;
    }

    public function setFormError(string $form, string $msg, array $data = []): void
    {
        $_SESSION['form_errors'][$form] = $msg;
        $_SESSION['form_data'][$form] = $data;
    }

    public function popFormError(string $form): ?string
    {
        $error = $_SESSION['form_errors'][$form] ?? null;
        unset($_SESSION['form_errors'][$form]);

        return $error;
    }

    public function popFormData(string $form): array
    {
        $data = $_SESSION['form_data'][$form] ?? [];
        unset($_SESSION['form_data'][$form]);

        return $data;
    }

    public function addMessage(string $type, string $text): void
    {
        $_SESSION['messages'][] = [
            'type' => $type,
            'text' => $text,
        ];
    }

    public function getMessages(): array
    {
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        return $messages;
    }
}