<?php
/**
 * Permission — RBAC engine.
 *
 * Loads the current user's permission set once per request (from APCu or DB),
 * then answers can() queries in O(1) array-lookup time.
 *
 * Usage (from any Controller):
 *   $this->can('transactions.approve')
 *   $this->requirePermission('categories.create')
 *   $this->canAny(['transactions.approve', 'transactions.reject'])
 */
class Permission
{
    private static ?self $instance = null;

    /** Flat list of permission name strings for the loaded user. */
    private array $granted = [];

    /** Whether the loaded user holds the super_admin wildcard. */
    private bool $isSuperAdmin = false;

    private ?int $loadedUserId = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ── Loading ───────────────────────────────────────────────────────

    /**
     * Bootstrap permissions for the authenticated user.
     * Called once inside Controller::requireAuth().
     */
    public function boot(int $userId, int $roleId): void
    {
        if ($this->loadedUserId === $userId) {
            return; // Already loaded this request
        }

        $cacheKey = "rbac_perms_u{$userId}";
        $cached   = Cache::get($cacheKey);

        if ($cached !== null) {
            $this->granted       = $cached['granted'];
            $this->isSuperAdmin  = $cached['super'];
            $this->loadedUserId  = $userId;
            return;
        }

        $rows = Database::getInstance()->fetchAll(
            "SELECT p.name, r.name AS role_name
             FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             JOIN roles r             ON r.id = rp.role_id
             WHERE rp.role_id = :role_id",
            [':role_id' => $roleId]
        );

        $this->granted      = array_column($rows, 'name');
        $this->isSuperAdmin = !empty($rows) && $rows[0]['role_name'] === 'super_admin';
        $this->loadedUserId = $userId;

        Cache::set($cacheKey, [
            'granted' => $this->granted,
            'super'   => $this->isSuperAdmin,
        ], 300);
    }

    // ── Query API ─────────────────────────────────────────────────────
    
    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }

    public function can(string $permission): bool
    {
        if ($this->isSuperAdmin) return true;
        return in_array($permission, $this->granted, true);
    }

    public function canAny(array $permissions): bool
    {
        if ($this->isSuperAdmin) return true;
        foreach ($permissions as $p) {
            if (in_array($p, $this->granted, true)) return true;
        }
        return false;
    }

    public function canAll(array $permissions): bool
    {
        if ($this->isSuperAdmin) return true;
        foreach ($permissions as $p) {
            if (!in_array($p, $this->granted, true)) return false;
        }
        return true;
    }

    /** Return all granted permission names (for debug / permission matrix view). */
    public function all(): array
    {
        return $this->granted;
    }

    // ── Cache management ─────────────────────────────────────────────

    /** Call after changing a user's role so the next request gets fresh permissions. */
    public static function flushUser(int $userId): void
    {
        Cache::delete("rbac_perms_u{$userId}");
        if (self::$instance !== null && self::$instance->loadedUserId === $userId) {
            self::$instance->granted      = [];
            self::$instance->isSuperAdmin = false;
            self::$instance->loadedUserId = null;
        }
    }
}
