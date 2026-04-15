<?php
class Setting
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $rows = $this->db->fetchAll("SELECT `key`, `value`, `label`, `type` FROM settings ORDER BY id");
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row;
        }
        return $result;
    }

    public function get(string $key, string $default = ''): string
    {
        $row = $this->db->fetch(
            "SELECT `value` FROM settings WHERE `key` = :key",
            [':key' => $key]
        );
        return $row ? $row['value'] : $default;
    }

    public function set(string $key, string $value): void
    {
        $this->db->execute(
            "UPDATE settings SET `value` = :value WHERE `key` = :key",
            [':value' => $value, ':key' => $key]
        );
    }

    public function updateMany(array $keyValues): void
    {
        foreach ($keyValues as $key => $value) {
            $this->set($key, (string)$value);
        }
    }
}