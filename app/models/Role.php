<?php
/**
 * Role Model — CRUD for roles and the permission matrix UI.
 */
class Role
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM roles ORDER BY sort_order ASC"
        );
    }

    public function getById(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM roles WHERE id = :id",
            [':id' => $id]
        );
    }

    public function getByName(string $name): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM roles WHERE name = :name",
            [':name' => $name]
        );
    }

    /**
     * Returns every permission, annotated with whether each role holds it.
     * Shape: [ 'resource' => [ ['name'=>..., 'label_lao'=>..., 'roles'=>[1=>true, 2=>false, …]] ] ]
     */
    public function getPermissionMatrix(): array
    {
        $roles = $this->getAll();
        $roleIds = array_column($roles, 'id');

        $permissions = $this->db->fetchAll(
            "SELECT * FROM permissions ORDER BY resource ASC, name ASC"
        );

        // Fetch all granted mappings in one query
        $granted = $this->db->fetchAll(
            "SELECT role_id, permission_id FROM role_permissions"
        );
        $grantedMap = [];
        foreach ($granted as $g) {
            $grantedMap[$g['role_id']][$g['permission_id']] = true;
        }

        $matrix = [];
        foreach ($permissions as $perm) {
            $row = [
                'id'        => $perm['id'],
                'name'      => $perm['name'],
                'label_lao' => $perm['label_lao'],
                'resource'  => $perm['resource'],
                'roles'     => [],
            ];
            foreach ($roleIds as $rid) {
                $row['roles'][$rid] = isset($grantedMap[$rid][$perm['id']]);
            }
            $matrix[$perm['resource']][] = $row;
        }

        return ['roles' => $roles, 'matrix' => $matrix];
    }

    /**
     * Sync the full permission set for a role.
     * $permissionIds = array of permission IDs to grant (others are revoked).
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $role = $this->getById($roleId);
        if (!$role || $role['is_system']) return; // System roles are read-only

        $this->db->beginTransaction();
        try {
            $this->db->execute(
                "DELETE FROM role_permissions WHERE role_id = :rid",
                [':rid' => $roleId]
            );
            foreach ($permissionIds as $pid) {
                $this->db->execute(
                    "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:rid, :pid)",
                    [':rid' => $roleId, ':pid' => (int) $pid]
                );
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Get the role_id for a given user. */
    public function getRoleIdForUser(int $userId): ?int
    {
        $row = $this->db->fetch(
            "SELECT role_id FROM users WHERE id = :id",
            [':id' => $userId]
        );
        return $row ? ((int) $row['role_id'] ?: null) : null;
    }
}
