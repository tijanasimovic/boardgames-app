<?php
// app/controllers/ReviewController.php
declare(strict_types=1);

require_once __DIR__ . '/../dao/ReviewDAO.php';
require_once __DIR__ . '/../lib/flash.php';

class ReviewController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

   
    public function add(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        if (empty($_SESSION['user'])) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok'=>false,'errors'=>['Niste prijavljeni.']], JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId   = (int)$_SESSION['user']['id'];
        $gameId   = (int)($_POST['game_id'] ?? 0);
        $rating   = (int)($_POST['rating'] ?? 0);
        $comment  = trim((string)($_POST['comment'] ?? ''));
        $redirect = $_POST['redirect'] ?? ('?page=game&id='.$gameId.'#reviews');

        $errors = [];
        if ($gameId <= 0)               $errors[] = 'Nedostaje identifikator igre.';
        if ($rating < 1 || $rating > 5) $errors[] = 'Ocena mora biti 1–5.';
        if ($comment === '')            $errors[] = 'Komentar je obavezan.';
        if (mb_strlen($comment) > 1000) $errors[] = 'Komentar je predugačak.';

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($errors) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok'=>false,'errors'=>$errors], JSON_UNESCAPED_UNICODE);
                return;
            }
            flash_set('err', implode(' ', $errors));
            header('Location: '.$redirect); return;
        }

        try {
            $dao = new ReviewDAO($this->pdo);
            $dao->upsertByUser($userId, $gameId, $rating, $comment);
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
                return;
            }
            flash_set('ok', 'Vaša recenzija je sačuvana.');
            header('Location: '.$redirect);
        } catch (Throwable $e) {
            error_log('review_add error: '.$e->getMessage());
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok'=>false,'errors'=>['Došlo je do greške pri čuvanju.']], JSON_UNESCAPED_UNICODE);
                return;
            }
            flash_set('err', 'Došlo je do greške pri čuvanju.');
            header('Location: '.$redirect);
        }
    }

  
    public function delete(): void {
        if (empty($_SESSION['user'])) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            return;
        }

        $reviewId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($reviewId <= 0) {
            flash_set('err', 'Nedostaje identifikator recenzije.');
            header('Location: ?page=games');
            exit;
        }

        $dao    = new ReviewDAO($this->pdo);
        $review = $dao->getById($reviewId);

        if (!$review) {
            flash_set('err', 'Recenzija ne postoji ili je već obrisana.');
            header('Location: ?page=games');
            exit;
        }

        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);
        $isAdmin       = !empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

        if ($review['user_id'] !== $currentUserId && !$isAdmin) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            return;
        }

        if ($dao->softDelete($reviewId)) {
            flash_set('ok', 'Recenzija je obrisana.');
        } else {
            flash_set('err', 'Brisanje nije uspelo.');
        }

        header('Location: ?page=game&id=' . (int)$review['game_id'] . '#reviews');
        exit;
    }

 
    public function myReviews(): void {
        if (empty($_SESSION['user'])) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            return;
        }
       
    }
}