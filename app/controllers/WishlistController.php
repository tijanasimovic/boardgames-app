<?php
// app/controllers/WishlistController.php
declare(strict_types=1);

require_once __DIR__ . '/../dao/WishlistDAO.php';

class WishlistController {
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    // /?page=wishlist
    public function list(): void {
        if (!isset($_SESSION['user'])) { http_response_code(403); echo "<h3>Prijavite se.</h3>"; return; }
        $uid = (int)$_SESSION['user']['id'];
        $dao = new WishlistDAO($this->pdo);
        $games = $dao->listForUser($uid);
        include __DIR__ . '/../views/wishlist/list.php';
    }
}
