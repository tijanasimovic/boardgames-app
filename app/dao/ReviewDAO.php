<?php
// app/dao/ReviewDAO.php
declare(strict_types=1);

class ReviewDAO {
    private PDO $pdo;

    public function __construct(PDO $pdo) { 
        $this->pdo = $pdo; 
    }

   
    public function getById(int $id): ?array {
        $sql = "SELECT r.*, u.username
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.id = :id AND r.is_deleted = 0
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

  
    public function forGame(int $gameId): array {
        $sql = "SELECT r.id, r.user_id, r.game_id, r.rating, r.comment, r.created_at,
                       u.username
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.game_id = :gid AND r.is_deleted = 0
                ORDER BY r.created_at DESC";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':gid', $gameId, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

   
    public function forGamePaged(int $gameId, int $page = 1, int $perPage = 10): array {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        $sql = "SELECT r.id, r.user_id, r.game_id, r.rating, r.comment, r.created_at,
                       u.username
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.game_id = :gid AND r.is_deleted = 0
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':gid', $gameId, PDO::PARAM_INT);
        $st->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

 
    public function countForGame(int $gameId): int {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM reviews WHERE game_id = :gid AND is_deleted = 0"
        );
        $st->bindValue(':gid', $gameId, PDO::PARAM_INT);
        $st->execute();
        return (int)$st->fetchColumn();
    }

   
    public function aggregateForGame(int $gameId): array {
        $sql = "SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS cnt
                FROM reviews
                WHERE game_id = :gid AND is_deleted = 0";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':gid', $gameId, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: ['avg_rating' => null, 'cnt' => 0];
    }

   
    public function findByUserAndGame(int $userId, int $gameId): ?array {
        $sql = "SELECT * FROM reviews
                WHERE user_id = :uid AND game_id = :gid AND is_deleted = 0
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':uid', $userId, PDO::PARAM_INT);
        $st->bindValue(':gid', $gameId, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

 
    public function add(int $userId, int $gameId, int $rating, string $comment): int {
        $sql = "INSERT INTO reviews (user_id, game_id, rating, comment, is_deleted, created_at)
                VALUES (:uid, :gid, :rating, :comment, 0, NOW())";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':uid', $userId, PDO::PARAM_INT);
        $st->bindValue(':gid', $gameId, PDO::PARAM_INT);
        $st->bindValue(':rating', $rating, PDO::PARAM_INT);
        $st->bindValue(':comment', $comment, PDO::PARAM_STR);
        $st->execute();
        return (int)$this->pdo->lastInsertId();
    }

  
    public function update(int $id, int $rating, string $comment): bool {
        $sql = "UPDATE reviews
                SET rating = :rating, comment = :comment
                WHERE id = :id AND is_deleted = 0";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':rating', $rating, PDO::PARAM_INT);
        $st->bindValue(':comment', $comment, PDO::PARAM_STR);
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        return $st->execute();
    }

    
    public function upsertByUser(int $userId, int $gameId, int $rating, string $comment): void {
        $sql = "INSERT INTO reviews (user_id, game_id, rating, comment, is_deleted, created_at)
                VALUES (:uid, :gid, :rating, :comment, 0, NOW())
                ON DUPLICATE KEY UPDATE
                    rating = VALUES(rating),
                    comment = VALUES(comment),
                    is_deleted = 0";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':uid', $userId, PDO::PARAM_INT);
        $st->bindValue(':gid', $gameId, PDO::PARAM_INT);
        $st->bindValue(':rating', $rating, PDO::PARAM_INT);
        $st->bindValue(':comment', $comment, PDO::PARAM_STR);
        $st->execute();
    }

  
    public function upsertReview(int $userId, int $gameId, int $rating, string $comment): void {
        $this->upsertByUser($userId, $gameId, $rating, $comment);
    }

  
    public function softDelete(int $id): bool {
        $st = $this->pdo->prepare("UPDATE reviews SET is_deleted = 1 WHERE id = :id");
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        return $st->execute();
    }
   
public function byUser(int $userId): array {
    $sql = "SELECT r.id, r.user_id, r.game_id, r.rating, r.comment, r.created_at, r.is_deleted,
                   u.username,
                   g.title AS game_title
            FROM reviews r
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN games g ON g.id = r.game_id
            WHERE r.user_id = :uid AND r.is_deleted = 0
            ORDER BY r.created_at DESC";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':uid', $userId, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

}