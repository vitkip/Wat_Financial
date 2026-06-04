<?php
/**
 * Cache — APCu with $_SESSION fallback.
 * No Redis/Memcached required for single-server deployment.
 */
class Cache
{
    private static ?bool $apcuAvailable = null;

    private static function apcu(): bool
    {
        if (self::$apcuAvailable === null) {
            self::$apcuAvailable = function_exists('apcu_enabled') && apcu_enabled();
        }
        return self::$apcuAvailable;
    }

    public static function get(string $key): mixed
    {
        if (self::apcu()) {
            $val = apcu_fetch($key, $ok);
            return $ok ? $val : null;
        }
        // Session fallback: check TTL stored alongside value
        $entry = $_SESSION['_cache'][$key] ?? null;
        if ($entry && time() < $entry['exp']) {
            return $entry['val'];
        }
        return null;
    }

    public static function set(string $key, mixed $val, int $ttl = 300): void
    {
        if (self::apcu()) {
            apcu_store($key, $val, $ttl);
            return;
        }
        $_SESSION['_cache'][$key] = ['val' => $val, 'exp' => time() + $ttl];
    }

    public static function delete(string $key): void
    {
        if (self::apcu()) {
            apcu_delete($key);
        }
        unset($_SESSION['_cache'][$key]);
    }

    /**
     * Fetch from cache or compute-and-store in one call.
     * Usage: Cache::remember('key', 300, fn() => expensiveQuery())
     */
    public static function remember(string $key, int $ttl, callable $compute): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }
        $result = $compute();
        self::set($key, $result, $ttl);
        return $result;
    }

    /** Bust all cache entries whose key starts with $prefix. */
    public static function deleteByPrefix(string $prefix): void
    {
        if (self::apcu()) {
            $info = apcu_cache_info(false);
            foreach (($info['cache_list'] ?? []) as $entry) {
                $k = $entry['info'] ?? $entry['key'] ?? '';
                if (str_starts_with($k, $prefix)) {
                    apcu_delete($k);
                }
            }
        }
        // Session fallback
        foreach (array_keys($_SESSION['_cache'] ?? []) as $k) {
            if (str_starts_with($k, $prefix)) {
                unset($_SESSION['_cache'][$k]);
            }
        }
    }
}
