<?php
// app/controllers/GameController.php
declare(strict_types=1);

require_once __DIR__ . '/../dao/GameDAO.php';
require_once __DIR__ . '/../dao/ReviewDAO.php';
require_once __DIR__ . '/../dao/WishlistDAO.php';
require_once __DIR__ . '/../dao/GenreDAO.php';
require_once __DIR__ . '/../lib/flash.php';

class GameController {
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    // controllers/GamesController.php (primer)
    public function index(): void {
        $dao  = new GameDAO($this->pdo);
        $gdao = new GenreDAO($this->pdo);

        
        $q = trim((string)($_GET['q'] ?? ''));

        
        $genreIds = [];
        if (isset($_GET['genre_id'])) {
            $raw = $_GET['genre_id'];
            if (!is_array($raw)) { $raw = [$raw]; }
            foreach ($raw as $gid) {
                $gid = (int)$gid;
                if ($gid > 0) { $genreIds[] = $gid; }
            }
            $genreIds = array_values(array_unique($genreIds));
        }

        $ratingMin = (isset($_GET['rating_min']) && $_GET['rating_min'] !== '') ? (int)$_GET['rating_min'] : null;

        $allowedSort = ['','rating_desc','rating_asc','name_asc','name_desc'];
        $sort = $_GET['sort'] ?? '';
        if (!in_array($sort, $allowedSort, true)) { $sort = ''; }

        // 2) Učitavanje
        try {
            
            $games  = $dao->listForIndex($q, $genreIds, $ratingMin, $sort);
        
            $genres = $gdao->all();
        } catch (PDOException $e) {
            $games  = [];
            $genres = [];
            flash_set('err', 'Greška pri učitavanju liste igara.');
        }

        // 3) Prosledi filtre view-u (genre_id je NIZ!)
        $filters = [
            'q'          => $q,
            'genre_id'   => $genreIds,
            'rating_min' => $ratingMin,
            'sort'       => $sort,
        ];

        include __DIR__ . '/../views/games/list.php';
    }

 
    public function list(): void {
        $dao  = new GameDAO($this->pdo);
        $gdao = new GenreDAO($this->pdo);

  
        $q         = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        
        $genreIds  = [];
        if (isset($_GET['genre_id'])) {
            $raw = $_GET['genre_id'];
            if (!is_array($raw)) { $raw = [$raw]; }
            foreach ($raw as $gid) {
                $gid = (int)$gid;
                if ($gid > 0) { $genreIds[] = $gid; }
            }
            // ukloni duplikate
            $genreIds = array_values(array_unique($genreIds));
        }

        $ratingMin = (isset($_GET['rating_min']) && $_GET['rating_min'] !== '') ? (int)$_GET['rating_min'] : null;

        // Dozvoljene vrednosti za sort (whitelist)
        $allowedSort = ['','rating_desc','rating_asc','name_asc','name_desc'];
        $sort = $_GET['sort'] ?? '';
        if (!in_array($sort, $allowedSort, true)) { $sort = ''; }

        try {
        
            $games  = $dao->listForIndex($q, $genreIds, $ratingMin, $sort);
            $genres = $gdao->all();
        } catch (PDOException $e) {
            $games  = [];
            $genres = [];
            flash_set('err', 'Greška pri učitavanju liste igara.');
        }

      
        $filters = [
            'q'          => $q,
            'genre_id'   => $genreIds,   
            'rating_min' => $ratingMin,
            'sort'       => $sort,
        ];

        include __DIR__ . '/../views/games/list.php';
    }

  
    public function top(): void {
        $dao = new GameDAO($this->pdo);
        $by   = $_GET['by'] ?? 'rating';
        $page = max(1, (int)($_GET['p'] ?? 1));
        $per  = 10;

        try {
            $result = $dao->topPaged($by, $page, $per);
            $games  = $result['rows'];
            $total  = $result['total'];
            $pages  = max(1, (int)ceil($total / $per));
        } catch (PDOException $e) {
            $games = [];
            $total = 0;
            $pages = 1;
            flash_set('err', 'Greška pri učitavanju top liste.');
        }

        $byTitle = ($by === 'comments') ? 'Najkomentarisanije igre' : 'Najbolje ocenjene igre';
        $filters = compact('by','page','pages','total','per');

        include __DIR__ . '/../views/games/top.php';
    }

   
    public function detail(): void {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $gdao = new GameDAO($this->pdo);
        $rdao = new ReviewDAO($this->pdo);
        $wdao = new WishlistDAO($this->pdo);

        try {
            $game = $gdao->find($id);
        } catch (PDOException $e) {
            $game = null;
        }

        if (!$game) {
            http_response_code(404);
            include __DIR__ . '/../views/errors/404.php';
            return;
        }

        // Žanrovi te igre
        $gdao2 = new GenreDAO($this->pdo);
        $game['_genres'] = $gdao2->forGame($id);

        // Agregat ocena
        try {
            $agg = $rdao->aggregateForGame($id);
        } catch (PDOException $e) {
            $agg = ['avg_rating'=>0,'cnt'=>0];
            flash_set('err', 'Greška pri učitavanju ocena.');
        }

        // Paginacija recenzija
        $pageR  = max(1, (int)($_GET['rp'] ?? 1));
        $perR   = 10;
        try {
            $totalR = $rdao->countForGame($id);
            $reviews= $rdao->forGamePaged($id, $pageR, $perR);
            $pagesR = max(1, (int)ceil($totalR / $perR));
        } catch (PDOException $e) {
            $totalR = 0;
            $reviews= [];
            $pagesR = 1;
            flash_set('err', 'Greška pri učitavanju recenzija.');
        }
        $revPager = ['page'=>$pageR, 'pages'=>$pagesR, 'per'=>$perR, 'total'=>$totalR];

        // Wishlist status
        try {
            $inWishlist = isset($_SESSION['user']) ? $wdao->has((int)$_SESSION['user']['id'], $id) : false;
        } catch (PDOException $e) {
            $inWishlist = false;
        }

        include __DIR__ . '/../views/games/detail.php';
    }

 
    public function wishlistToggle(): void {
        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            flash_set('err', 'Prijavite se da biste koristili Wishlist.');
            header('Location: ?page=login'); exit;
        }

        $uid = (int)$_SESSION['user']['id'];
        $gid = (int)($_POST['game_id'] ?? 0);

        if ($gid <= 0) {
            flash_set('err', 'Neispravan zahtev.');
            header('Location: ?page=games'); exit;
        }

        $wdao = new WishlistDAO($this->pdo);

        try {
            if ($wdao->has($uid, $gid)) {
                $wdao->remove($uid, $gid);
                flash_set('ok', 'Igra je uklonjena sa Wishlist-a.');
            } else {
                $wdao->add($uid, $gid);
                flash_set('ok', 'Igra je dodata na Wishlist.');
            }
        } catch (PDOException $e) {
            flash_set('err', 'Greška pri ažuriranju Wishlist-a.');
        }

        header('Location: ?page=game&id='.$gid);
        exit;
    }
}