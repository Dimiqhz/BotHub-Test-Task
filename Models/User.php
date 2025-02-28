<?php

namespace Models;

use PDO;

class User {

    protected $pdo;
    protected $driver;

    public function __construct(PDO $pdo, string $driver = 'mysql') {
        $this->pdo = $pdo;
        $this->driver = $driver;
        $this->createTable();
    }

    protected function createTable(): void {
        if ($this->driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                        id SERIAL PRIMARY KEY,
                        telegram_id BIGINT UNIQUE,
                        balance NUMERIC(10,2) NOT NULL DEFAULT 0.00,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        telegram_id BIGINT UNIQUE,
                        balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        }
        $this->pdo->exec($sql);
    }

    public function findOrCreate(int $telegramId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE telegram_id = :telegram_id");
        $stmt->bindValue(':telegram_id', $telegramId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $stmt = $this->pdo->prepare("INSERT INTO users (telegram_id, balance) VALUES (:telegram_id, 0.00)");
            $stmt->bindValue(':telegram_id', $telegramId, PDO::PARAM_INT);
            $stmt->execute();
            return ['telegram_id' => $telegramId, 'balance' => 0.00];
        }
        return $user;
    }

    public function updateBalance(int $telegramId, float $newBalance): void {
        $stmt = $this->pdo->prepare("UPDATE users SET balance = :balance WHERE telegram_id = :telegram_id");
        $stmt->bindValue(':balance', number_format($newBalance, 2, '.', ''), PDO::PARAM_STR);
        $stmt->bindValue(':telegram_id', $telegramId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function applyTransaction(int $telegramId, float $amount): array {
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("SELECT balance FROM users WHERE telegram_id = :telegram_id FOR UPDATE");
        $stmt->bindValue(':telegram_id', $telegramId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $user ? (float)$user['balance'] : 0.00;

        if (!$user) {
            $stmt = $this->pdo->prepare("INSERT INTO users (telegram_id, balance) VALUES (:telegram_id, 0.00)");
            $stmt->bindValue(':telegram_id', $telegramId, PDO::PARAM_INT);
            $stmt->execute();
        }
        $newBalance = $balance + $amount;

        if ($newBalance < 0) {
            $this->pdo->rollBack();
            return ['error' => "Ошибка: недостаточно средств. Текущий баланс: " . number_format($balance, 2)];
        }
        
        $this->updateBalance($telegramId, $newBalance);
        $this->pdo->commit();
        
        return ['balance' => $newBalance];
    }
}
