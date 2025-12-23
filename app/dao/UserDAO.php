<?php
// app/dao/UserDAO.php
declare(strict_types=1);

require_once __DIR__ . '/BaseDAO.php';

class UserDAO extends BaseDAO {


    public function findByUsername(string $username): ?array {
        $st = $this->pdo->prepare("SELECT id, username, email, password, role FROM users WHERE username = ?");
        $st->execute([$username]);
        $u = $st->fetch();
        return $u ?: null;
    }

  
    public function findByUsernameOrEmail(string $username, string $email): ?array {
        $st = $this->pdo->prepare("SELECT id, username, email FROM users WHERE username = ? OR email = ?");
        $st->execute([$username, $email]);
        $u = $st->fetch();
        return $u ?: null;
    }

   
    public function create(string $username, string $email, string $password, string $role='user'): int {
        $st = $this->pdo->prepare(
            "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)"
        );
        $st->execute([$username, $email, $password, $role]);
        return (int)$this->pdo->lastInsertId();
    }

   
    public function findById(int $id): ?array {
        $st = $this->pdo->prepare("SELECT id, username, email, password, role FROM users WHERE id = ?");
        $st->execute([$id]);
        $u = $st->fetch();
        return $u ?: null;
    }
}
