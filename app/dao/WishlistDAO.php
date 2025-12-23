<?php
// app/dao/WishlistDAO.php
declare(strict_types=1);

class WishlistDAO {
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function has(int $userId, int $gameId): bool {
        $st = $this->pdo->prepare("SELECT 1 FROM wishlists WHERE user_id=? AND game_id=?");
        $st->execute([$userId, $gameId]);
        return (bool)$st->fetchColumn();
    }

    public function add(int $userId, int $gameId): void {
        $st = $this->pdo->prepare("INSERT IGNORE INTO wishlists(user_id, game_id) VALUES (?,?)");
        $st->execute([$userId, $gameId]);
    }

    public function remove(int $userId, int $gameId): void {
        $st = $this->pdo->prepare("DELETE FROM wishlists WHERE user_id=? AND game_id=?");
        $st->execute([$userId, $gameId]);
    }

    
    public function listForUser(int $userId): array {
        $sql = "SELECT g.*
                FROM wishlists w
                JOIN games g ON g.id = w.game_id
                WHERE w.user_id = ?
                ORDER BY g.title ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([$userId]);
        return $st->fetchAll();
    }
}
