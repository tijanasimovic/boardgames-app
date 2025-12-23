<?php
// app/controllers/AdminGenreController.php
declare(strict_types=1);

require_once __DIR__ . '/../dao/GenreDAO.php';

class AdminGenreController {
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403); echo "<h2>Zabranjen pristup (admin)</h2>"; exit;
        }
    }

    // /?page=admin_genres (GET lista, POST kreiranje, GET delete)
    public function list(): void {
        $this->requireAdmin();
        $dao = new GenreDAO($this->pdo);

        // kreiranje
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            if ($name !== '') { $dao->create($name); }
            header('Location: ?page=admin_genres'); exit;
        }

        // brisanje
        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            if ($id>0) $dao->delete($id);
            header('Location: ?page=admin_genres'); exit;
        }

        $genres = $dao->all();
        include __DIR__ . '/../views/admin/genres.php';
    }
}
