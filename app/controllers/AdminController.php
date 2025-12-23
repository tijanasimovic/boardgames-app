<?php
// app/controllers/AdminController.php
declare(strict_types=1);

require_once __DIR__ . '/../dao/GameDAO.php';
require_once __DIR__ . '/../dao/GenreDAO.php';
require_once __DIR__ . '/../lib/flash.php';

class AdminController {
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    // /?page=admin
    public function dashboard(): void {
        $this->requireAdmin();
        include __DIR__ . '/../views/admin/dashboard.php';
    }

    // /?page=admin_games  (GET lista + forma, POST čuvanje, GET delete)
    public function games(): void {
        $this->requireAdmin();
        $dao  = new GameDAO($this->pdo);
        $gdao = new GenreDAO($this->pdo);
        $allGenres = $gdao->all();

        // DELETE
        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            if ($id > 0) {
                $dao->delete($id);
                flash_set('ok', "Igra #$id obrisana.");
            } else {
                flash_set('err', "Neispravan ID za brisanje.");
            }
            header('Location: ?page=admin_games'); exit;
        }

        // INSERT/UPDATE
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;

            
            $existing = null;
            if ($id) {
                $existing = $dao->find($id);
            }

            $imagePath = trim((string)($_POST['image_path'] ?? ''));

            if ($id) {
              
                if ($imagePath === '') {
                    $imagePath = $existing['image_path'] ?? 'assets/default_game.png';
                }
            } else {
                
                if ($imagePath === '') {
                    $imagePath = 'assets/default_game.png';
                }
            }

            $data = [
                'title'        => trim((string)($_POST['title'] ?? '')),
                'description'  => trim((string)($_POST['description'] ?? '')),
                'min_players'  => (int)($_POST['min_players'] ?? 1),
                'max_players'  => (int)($_POST['max_players'] ?? 4),
                'play_time'    => (int)($_POST['play_time'] ?? 60),
                'year'         => (int)($_POST['year'] ?? date('Y')),
                'image_path'   => $imagePath,
            ];

            $newId = $dao->upsert($id, $data);

            $selGenres = (isset($_POST['genres']) && is_array($_POST['genres'])) ? $_POST['genres'] : [];
            $gdao->setForGame($newId, $selGenres);

            flash_set('ok', $id ? "Igra #$id izmenjena." : "Nova igra dodata (#$newId).");
            header('Location: ?page=admin_games'); exit;
        }

        // LISTA + FORMA
        $games = $dao->all();
        foreach ($games as &$g) {
            $g['_genres'] = $gdao->forGame((int)$g['id']);
        }
        unset($g);

        include __DIR__ . '/../views/admin/games.php';
    }
}
