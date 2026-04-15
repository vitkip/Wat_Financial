<?php
/**
 * Base Controller
 * Provides view rendering helpers, CSRF protection, and flash messages.
 */
class Controller
{
    /**
     * Render a view inside the main layout.
     *
     * @param string $view   Path relative to app/views/, e.g. 'dashboard/index'
     * @param array  $data   Variables to extract into the view scope
     * @param string $title  Page title (injected into <title>)
     * @param string $active Active nav item key
     */
    protected function view(
        string $view,
        array  $data   = [],
        string $title  = '',
        string $active = 'dashboard',
        string $layout = 'main'
    ): void {
        extract($data);

        $pageTitle  = $title ?: APP_NAME;
        $activeNav  = $active;
        $authUser   = $this->currentUser();
        $flash      = $this->getFlash();        // ດຶງ flash message ສຳລັບ view
        $csrfToken  = $this->csrfToken();       // ສ້າງ / ດຶງ CSRF token
        $viewFile   = BASE_PATH . '/app/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View not found: {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require BASE_PATH . '/app/views/layouts/' . $layout . '.php';
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth');
        }
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    protected function currentUser(): array
    {
        return [
            'id'    => $_SESSION['user_id']   ?? null,
            'name'  => $_SESSION['user_name'] ?? 'ຜູ້ໃຊ້',
            'email' => $_SESSION['user_email'] ?? '',
        ];
    }

    protected function formatCurrency(float $amount): string
    {
        $dp = defined('DECIMAL_PLACES') ? (int) DECIMAL_PLACES : 0;
        return CURRENCY . ' ' . number_format($amount, $dp, '.', ',');
    }

    // ──────────────────────────────────────────────────────────
    // CSRF Protection
    // ──────────────────────────────────────────────────────────

    /**
     * ສ້າງ / ດຶງ CSRF token ຈາກ session.
     */
    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * ກວດ CSRF token ທີ່ POST ມາ. ຖ້າຜ່ານ → 403.
     */
    protected function verifyCsrf(): void
    {
        $token  = $_POST['_csrf'] ?? '';
        $stored = $_SESSION['csrf_token'] ?? '';
        if (!$stored || !hash_equals($stored, $token)) {
            http_response_code(403);
            die('<h1>403 — ການຮ້ອງຂໍໝົດອາຍຸ ຫຼື ບໍ່ຖືກຕ້ອງ. <a href="javascript:history.back()">ກັບຄືນ</a></h1>');
        }
        // rotate token ຫຼັງໃຊ້
        unset($_SESSION['csrf_token']);
    }

    // ──────────────────────────────────────────────────────────
    // Flash Messages (session-based, cleared on first read)
    // ──────────────────────────────────────────────────────────

    /**
     * ຕັ້ງ flash message.
     * @param string $type  'success' | 'error' | 'info' | 'warning'
     * @param string $msg   ຂໍ້ຄວາມ
     */
    protected function flash(string $type, string $msg): void
    {
        $_SESSION['_flash'] = ['type' => $type, 'msg' => $msg];
    }

    /**
     * ດຶງ ແລ້ວ ລຶບ flash message.
     * @return array|null ['type'=>..., 'msg'=>...] ຫຼື null
     */
    protected function getFlash(): ?array
    {
        if (!empty($_SESSION['_flash'])) {
            $flash = $_SESSION['_flash'];
            unset($_SESSION['_flash']);
            return $flash;
        }
        return null;
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    /**
     * Validate email format.
     */
    protected function isValidEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * ກວດ type ຂອງ transaction.
     */
    protected function sanitizeType(string $raw): string
    {
        return in_array($raw, ['income', 'expense'], true) ? $raw : 'expense';
    }
}
