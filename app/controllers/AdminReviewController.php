<?php
// app/controllers/AdminReviewController.php
declare(strict_types=1);

require_once __DIR__ . '/../lib/flash.php';

class AdminReviewController {
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? null) !== 'admin') {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    // /?page=admin_reviews[&q=...][&status=all|visible|hidden][&p=1]
    public function list(): void {
        $this->requireAdmin();

        $q       = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $status  = $_GET['status'] ?? 'all';                  
        $allowed = ['all','visible','hidden'];
        if (!in_array($status, $allowed, true)) { $status = 'all'; }

        $page   = max(1, (int)($_GET['p'] ?? 1));
        $per    = 20;
        $offset = ($page - 1) * $per;

        $where  = [];
        $params = [];

        if ($q !== '') {
            $where[] = "(g.title LIKE :q OR u.username LIKE :q)";
            $params[':q'] = "%".$q."%";
        }
        if ($status === 'visible') {
            $where[] = "r.is_deleted = 0";
        } elseif ($status === 'hidden') {
            $where[] = "r.is_deleted = 1";
        }
        $whereSql = $where ? ("WHERE ".implode(" AND ", $where)) : "";

        try {
            // ukupno za paginaciju
            $countSql = "SELECT COUNT(*)
                         FROM reviews r
                         JOIN users u ON u.id = r.user_id
                         JOIN games g ON g.id = r.game_id
                         $whereSql";
            $stc = $this->pdo->prepare($countSql);
            $stc->execute($params);
            $total = (int)$stc->fetchColumn();
            $pages = max(1, (int)ceil($total / $per));

            // glavna lista
            $sql = "SELECT r.*, u.username, g.title
                    FROM reviews r
                    JOIN users u ON u.id = r.user_id
                    JOIN games g ON g.id = r.game_id
                    $whereSql
                    ORDER BY r.created_at DESC
                    LIMIT :lim OFFSET :off";
            $st = $this->pdo->prepare($sql);
            foreach ($params as $k=>$v) {
                $st->bindValue($k, $v, PDO::PARAM_STR);
            }
            $st->bindValue(':lim', $per, PDO::PARAM_INT);
            $st->bindValue(':off', $offset, PDO::PARAM_INT);
            $st->execute();
            $rows = $st->fetchAll();

        } catch (PDOException $e) {
            $rows = [];
            $total = 0;
            $pages = 1;
            flash_set('err', 'Greška pri učitavanju recenzija.');
        }

        // prosledi u view parametre da forma zadrži filtere
        $filters = [
            'q'=>$q, 'status'=>$status,
            'page'=>$page, 'pages'=>$pages, 'total'=>$total, 'per'=>$per
        ];
        include __DIR__ . '/../views/admin/reviews.php';
    }

    // /?page=admin_reviews_hide (POST id)
    public function hide(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            flash_set('err', 'Neispravan ID recenzije.');
            header('Location: ?page=admin_reviews'); exit;
        }

        try {
            $st = $this->pdo->prepare("UPDATE reviews SET is_deleted = 1 WHERE id = ?");
            $st->execute([$id]);
            $aff = $st->rowCount();
            if ($aff > 0) flash_set('ok', "Recenzija #$id je sakrivena.");
            else          flash_set('err', "Recenzija #$id nije pronađena.");
        } catch (PDOException $e) {
            flash_set('err', 'Greška pri sakrivanju recenzije.');
        }

        header('Location: ?page=admin_reviews'); exit;
    }

    // /?page=admin_reviews_restore (POST id)
    public function restore(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            flash_set('err', 'Neispravan ID recenzije.');
            header('Location: ?page=admin_reviews'); exit;
        }

        try {
            $st = $this->pdo->prepare("UPDATE reviews SET is_deleted = 0 WHERE id = ?");
            $st->execute([$id]);
            $aff = $st->rowCount();
            if ($aff > 0) flash_set('ok', "Recenzija #$id je vraćena.");
            else          flash_set('err', "Recenzija #$id nije pronađena.");
        } catch (PDOException $e) {
            flash_set('err', 'Greška pri vraćanju recenzije.');
        }

        header('Location: ?page=admin_reviews'); exit;
    }
}
