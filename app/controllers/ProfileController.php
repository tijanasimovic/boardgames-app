<?php
// app/controllers/ProfileController.php
declare(strict_types=1);

require_once __DIR__ . '/../dao/ReviewDAO.php';
require_once __DIR__ . '/../dao/WishlistDAO.php';

class ProfileController {
    private PDO $pdo;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    // GET /?page=profile
    public function overview(): void {
        if (empty($_SESSION['user'])) {
            http_response_code(403);
            echo "<h3>Prijavite se.</h3>";
            return;
        }

        $uid  = (int)$_SESSION['user']['id'];
        $rdao = new ReviewDAO($this->pdo);
        $wdao = new WishlistDAO($this->pdo);

        // Recenzije korisnika
        $myReviews  = $rdao->byUser($uid);
        // Lista želja
        $myWishlist = $wdao->listForUser($uid);

        
        $stats = [
            'reviews_count'  => count($myReviews),
            'wishlist_count' => count($myWishlist),
            'avg_rating'     => $this->avgRating($myReviews),
        ];

        //ACHIEVEMENTS (bedževi) 
        // 1) Organizovani događaji
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM events WHERE organizer_id = ?");
        $st->execute([$uid]);
        $organizedCount = (int)$st->fetchColumn();

        // 2) Prisustva na događajima 
        $st = $this->pdo->prepare("SELECT COUNT(*)
                                   FROM event_attendees
                                   WHERE user_id = ? AND TRIM(LOWER(status)) = 'going'");
        $st->execute([$uid]);
        $goingCount = (int)$st->fetchColumn();

        // Sastavi listu osvojenih bedževa
        $achievements = [];
        if ($stats['reviews_count'] >= 10) {
            $achievements[] = [
                'icon' => '⭐',
                'text' => 'Kritičar (10+)',
                'tip'  => 'Dobija se za 10 ili više napisanih recenzija.'
            ];
        }
        if ($organizedCount >= 1) {
            $achievements[] = [
                'icon' => '🎉',
                'text' => 'Organizator',
                'tip'  => 'Dobija se za organizaciju barem jednog događaja.'
            ];
        }
        if ($goingCount >= 5) {
            $achievements[] = [
                'icon' => '🧩',
                'text' => 'Društvenjak (5+)',
                'tip'  => 'Dobija se za potvrđen dolazak na 5 ili više događaja.'
            ];
        }
        // (po želji lako dodaš nove uslove i bedževe)

        // Prosledi sve u view
        include __DIR__ . '/../views/profile/overview.php';
    }

    private function avgRating(array $rows): ?float {
        if (!$rows) return null;
        $sum = 0;
        foreach ($rows as $r) { $sum += (int)($r['rating'] ?? 0); }
        return round($sum / count($rows), 1);
    }
}
