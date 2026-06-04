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
        if (empty($keyValues)) return;

        // Single CASE UPDATE instead of N individual UPDATEs
        $cases  = [];
        $params = [];
        $inKeys = [];
        $i      = 0;

        foreach ($keyValues as $key => $value) {
            $kp          = ":k{$i}";
            $vp          = ":v{$i}";
            $cases[]     = "WHEN {$kp} THEN {$vp}";
            $params[$kp] = $key;
            $params[$vp] = (string) $value;
            $inKeys[]    = $kp;
            $i++;
        }

        $inList = implode(', ', $inKeys);
        $this->db->execute(
            "UPDATE settings SET value = CASE `key` " . implode(' ', $cases) . " END
             WHERE `key` IN ({$inList})",
            $params
        );
    }
}