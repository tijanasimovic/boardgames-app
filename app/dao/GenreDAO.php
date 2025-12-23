<?php
// app/dao/GenreDAO.php
declare(strict_types=1);

class GenreDAO {
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function all(): array {
        return $this->pdo->query("SELECT id, name FROM genres ORDER BY name ASC")->fetchAll();
    }

    public function create(string $name): void {
        $st = $this->pdo->prepare("INSERT INTO genres(name) VALUES (?)");
        $st->execute([$name]);
    }

    public function delete(int $id): void {
        $st = $this->pdo->prepare("DELETE FROM genres WHERE id=?");
        $st->execute([$id]);
    }

    public function forGame(int $gameId): array {
        $sql = "SELECT g.id, g.name
                FROM game_genre gg
                JOIN genres g ON g.id = gg.genre_id
                WHERE gg.game_id = ?
                ORDER BY g.name ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([$gameId]);
        return $st->fetchAll();
    }


    public function setForGame(int $gameId, array $genreIds): void {
        $this->pdo->prepare("DELETE FROM game_genre WHERE game_id=?")->execute([$gameId]);
        if (!$genreIds) return;
        $ins = $this->pdo->prepare("INSERT INTO game_genre(game_id, genre_id) VALUES (?,?)");
        foreach ($genreIds as $gid) {
            $gid = (int)$gid;
            if ($gid>0) $ins->execute([$gameId, $gid]);
        }
    }
}
