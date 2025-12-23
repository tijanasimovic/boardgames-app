<?php
declare(strict_types=1);

use App\Controllers\AuthController;

session_start();

require __DIR__ . '/../app/dao/db.php';
require __DIR__ . '/../app/controllers/AuthController.php';
require __DIR__ . '/../app/controllers/GameController.php';
require __DIR__ . '/../app/controllers/AdminController.php';
require __DIR__ . '/../app/controllers/ReviewController.php';
require __DIR__ . '/../app/controllers/WishlistController.php';
require __DIR__ . '/../app/controllers/AdminReviewController.php';
require __DIR__ . '/../app/controllers/ProfileController.php';
require __DIR__ . '/../app/controllers/AdminGenreController.php';
require_once __DIR__ . '/../app/controllers/EventController.php';


$pdo = DB::createInstance();
$eventController = new EventController($pdo);

// Stranica koja se traži (default = home)
$page = $_GET['page'] ?? 'home';

switch ($page) {

    //   AUTH  
    case 'login':
        (new AuthController($pdo))->login();
        break;
    case 'register':
        (new AuthController($pdo))->register();
        break;
    case 'logout':
        (new AuthController($pdo))->logout();
        break;

    //   IGRE  
    case 'home': 
    case 'games':
        (new GameController($pdo))->list();
        break;

    case 'game':
        (new GameController($pdo))->detail();
        break;

    case 'top':
        (new GameController($pdo))->top();
        break;

    //   WISHLIST  
    case 'wishlist':
        (new WishlistController($pdo))->list();
        break;

    case 'wishlist_toggle': // POST iz detail forme
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new GameController($pdo))->wishlistToggle();
        } else {
            header('Location: /?page=game&id='.(int)($_GET['id'] ?? 0));
        }
        break;

    //   PROFIL  
    case 'profile':
        (new ProfileController($pdo))->overview();
        break;

    //   ADMIN  
    case 'admin':
        (new AdminController($pdo))->dashboard();
        break;

    case 'admin_games':
        (new AdminController($pdo))->games();
        break;

    case 'admin_reviews':
        (new AdminReviewController($pdo))->list();
        break;

    case 'admin_reviews_hide':    // POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminReviewController($pdo))->hide();
        } else {
            header('Location: /?page=admin_reviews');
        }
        break;

    case 'admin_reviews_restore': // POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminReviewController($pdo))->restore();
        } else {
            header('Location: /?page=admin_reviews');
        }
        break;

    case 'admin_genres':
        (new AdminGenreController($pdo))->list();
        break;
    case 'review_add':
      (new ReviewController($pdo))->add();
        break;
    case 'events':        (new EventController($pdo))->index();   break;
    case 'event_new':     (new EventController($pdo))->new();     break;
    case 'event_save':    if($_SERVER['REQUEST_METHOD']==='POST'){ (new EventController($pdo))->save(); } break;
    case 'event':         (new EventController($pdo))->show();    break;
    case 'event_rsvp':    (new EventController($pdo))->rsvp();    break;
    case 'event_checkin': (new EventController($pdo))->checkin(); break;
    case 'event_delete':  (new EventController($pdo))->delete();break;
    case 'review_delete':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new ReviewController($pdo))->delete();
    } else {
        header('Location: /?page=games');
    }
    break;
    // DEFAULT (fallback)
    default:
       
        header('Location: /?page=home');
        exit;
}
