<?php
// app/controllers/BaseController.php
declare(strict_types=1);

class BaseController {
    protected PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    protected function requireLogin(): void {
        if (!isset($_SESSION['user'])) {
            header('Location: ?page=login&msg=Prijavite se da biste pristupili.');
            exit;
        }
    }

    protected function requireOrganizerOrAdmin(int $organizerId): void {
        $this->requireLogin();
        $u = $_SESSION['user'];
        if (($u['role'] ?? '') !== 'admin' && (int)$u['id'] !== (int)$organizerId) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }
}