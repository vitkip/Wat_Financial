<?php
/**
 * Database — PDO Singleton
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;
    private int $transactionCounter = 0;

    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this instead of exposing the message
            if (APP_ENV === 'development') {
                die('Database connection failed: ' . $e->getMessage());
            } else {
                die('Database connection failed. Please try again later.');
            }
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        if ($this->transactionCounter === 0) {
            $this->pdo->beginTransaction();
        }
        $this->transactionCounter++;
        return true;
    }

    public function commit(): bool
    {
        if ($this->transactionCounter > 0) {
            $this->transactionCounter--;
        }
        if ($this->transactionCounter === 0) {
            return $this->pdo->commit();
        }
        return true;
    }

    public function rollBack(): bool
    {
        if ($this->transactionCounter > 0) {
            $this->transactionCounter = 0;
            return $this->pdo->rollBack();
        }
        return false;
    }

    /**
     * Returns a live PDOStatement for row-by-row streaming.
     * Use with foreach() to avoid loading large result sets into memory.
     */
    public function cursor(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute($params);
        return $stmt;
    }
}
