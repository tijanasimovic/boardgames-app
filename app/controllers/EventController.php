<?php
// app/controllers/EventController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../dao/EventDAO.php';
require_once __DIR__ . '/../dao/GameDAO.php';

class EventController extends BaseController {

    // /?page=events
    public function index(): void {
        $this->requireLogin();
        $dao = new EventDAO($this->pdo);
        $events = $dao->listAllByDateAsc();
        include __DIR__ . '/../views/events/index.php';
    }

    // /?page=event_new (GET)
    public function new(): void {
        $this->requireLogin();
        $gdao  = new GameDAO($this->pdo);
        $games = $gdao->getAllMinimal();
        include __DIR__ . '/../views/events/new.php';
    }

    // /?page=event_save (POST)
    public function save(): void {
        $this->requireLogin();

        $title    = trim($_POST['title'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $isOnline = isset($_POST['is_online']) ? 1 : 0;
        $location = $isOnline ? null : trim($_POST['location'] ?? '');
        $online   = $isOnline ? trim($_POST['online_url'] ?? '') : null;
        $starts   = trim($_POST['starts_at'] ?? '');
        $ends     = trim($_POST['ends_at'] ?? '');
        $capacity = ($_POST['capacity'] ?? '') !== '' ? (int)$_POST['capacity'] : null;
        $gameIdRaw= $_POST['game_id'] ?? '';
        $game_id  = ($gameIdRaw !== '') ? (int)$gameIdRaw : null;

        $gdao = new GameDAO($this->pdo);

        if ($title === '' || $starts === '') {
            $error = 'Naslov i početak su obavezni.';
            $games = $gdao->getAllMinimal();
            include __DIR__ . '/../views/events/new.php';
            return;
        }
        if ($game_id !== null && !$gdao->findById($game_id)) {
            $error = 'Izabrana igra ne postoji.';
            $games = $gdao->getAllMinimal();
            include __DIR__ . '/../views/events/new.php';
            return;
        }

        $dao = new EventDAO($this->pdo);
        $id = $dao->create([
            'title'        => $title,
            'description'  => $desc ?: null,
            'location'     => $location ?: null,
            'online_url'   => $online ?: null,
            'starts_at'    => $starts,
            'ends_at'      => $ends ?: null,
            'capacity'     => $capacity,
            'game_id'      => $game_id,
            'organizer_id' => (int)$_SESSION['user']['id'],
            'is_online'    => $isOnline,
        ]);

        header("Location: ?page=event&id=".$id);
        exit;
    }

    // /?page=event&id=123
    public function show(): void {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);

        $dao   = new EventDAO($this->pdo);
        $event = $dao->findByIdWithJoins($id);
        if (!$event) { http_response_code(404); include __DIR__ . '/../views/errors/404.php'; return; }

        $attendees   = $dao->attendees($id);
        $goingCount  = $dao->countGoing($id); // samo potvrđeni
        include __DIR__ . '/../views/events/show.php';
    }

    // /?page=event_rsvp&id=123  (GET/POST) -> samo "going"
    public function rsvp(): void {
        $this->requireLogin();
        $eventId = (int)($_GET['id'] ?? 0);
        $userId  = (int)$_SESSION['user']['id'];

        $dao   = new EventDAO($this->pdo);
        $event = $dao->findByIdWithJoins($eventId);
        if (!$event) { http_response_code(404); include __DIR__.'/../views/errors/404.php'; return; }

        // organizator ne prijavljuje dolazak
        if ($userId === (int)$event['organizer_id']) {
            header("Location: ?page=event&id=".$eventId);
            exit;
        }

        // blokiraj ako je popunjeno (računamo samo going)
        $goingCount = $dao->countGoing($eventId);
        if (!empty($event['capacity']) && $goingCount >= (int)$event['capacity']) {
            $_SESSION['flash_warning'] = 'Događaj je popunjen — prijava nije moguća.';
            header("Location: ?page=event&id=".$eventId."#rsvp");
            exit;
        }

        // jedina validna akcija je potvrda dolaska
        $dao->upsertRsvp($eventId, $userId, 'going');

        header("Location: ?page=event&id=".$eventId."#rsvp");
        exit;
    }

    // /?page=event_checkin&id=123&user=45 (POST)
    public function checkin(): void {
        $this->requireLogin();
        $id  = (int)($_GET['id'] ?? 0);
        $uid = (int)($_GET['user'] ?? 0);

        $dao   = new EventDAO($this->pdo);
        $event = $dao->findByIdWithJoins($id);
        if (!$event) { http_response_code(404); include __DIR__ . '/../views/errors/404.php'; return; }

        $this->requireOrganizerOrAdmin((int)$event['organizer_id']);
        $dao->checkin($id, $uid);
        header("Location: ?page=event&id=".$id."#attendees");
        exit;
    }

    // /?page=event_delete&id=123
    public function delete(): void {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);

        $dao   = new EventDAO($this->pdo);
        $event = $dao->findByIdWithJoins($id);
        if (!$event) { http_response_code(404); include __DIR__ . '/../views/errors/404.php'; return; }

        $this->requireOrganizerOrAdmin((int)$event['organizer_id']);
        $dao->delete($id);
        header("Location: ?page=events");
        exit;
    }
}
