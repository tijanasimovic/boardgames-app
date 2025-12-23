<?php
// app/dao/EventDAO.php
declare(strict_types=1);

class EventDAO {
    private PDO $db;
    public function __construct(PDO $pdo){ $this->db = $pdo; }

    public function create(array $d): int {
        $sql = "INSERT INTO events
                (title,description,location,online_url,starts_at,ends_at,capacity,game_id,organizer_id,is_online)
                VALUES (:title,:description,:location,:online_url,:starts_at,:ends_at,:capacity,:game_id,:organizer_id,:is_online)";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':title'        => $d['title'],
            ':description'  => $d['description'],
            ':location'     => $d['location'],
            ':online_url'   => $d['online_url'],
            ':starts_at'    => $d['starts_at'],
            ':ends_at'      => $d['ends_at'],
            ':capacity'     => $d['capacity'],
            ':game_id'      => $d['game_id'],
            ':organizer_id' => $d['organizer_id'],
            ':is_online'    => $d['is_online'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    
    public function findByIdWithJoins(int $id): ?array {
        $st = $this->db->prepare(
            "SELECT e.*,
                    u.username AS organizer_name,
                    g.title    AS game_title
             FROM events e
             JOIN users u ON u.id = e.organizer_id
             LEFT JOIN games g ON g.id = e.game_id
             WHERE e.id=?"
        );
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function listUpcoming(int $limit=50): array {
        $st = $this->db->prepare(
            "SELECT e.id, e.title, e.starts_at, e.location, e.is_online,
                    u.username AS organizer_name,
                    g.title    AS game_title
             FROM events e
             JOIN users u ON u.id = e.organizer_id
             LEFT JOIN games g ON g.id = e.game_id
             WHERE e.starts_at >= NOW()
             ORDER BY e.starts_at ASC
             LIMIT ?"
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listAllByDateAsc(): array {
        $st = $this->db->query(
            "SELECT e.id, e.title, e.starts_at, e.location, e.is_online,
                    u.username AS organizer_name,
                    g.title    AS game_title
             FROM events e
             JOIN users u ON u.id = e.organizer_id
             LEFT JOIN games g ON g.id = e.game_id
             ORDER BY e.starts_at ASC"
        );
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): void {
        $st = $this->db->prepare("DELETE FROM events WHERE id=?");
        $st->execute([$id]);
    }

    
    public function upsertRsvp(int $eventId, int $userId, string $status='going'): void {

        $status = (trim(strtolower($status)) === 'going') ? 'going' : 'interested';

        $sql = "INSERT INTO event_attendees (event_id, user_id, status)
                VALUES (:e, :u, :s)
                ON DUPLICATE KEY UPDATE status = VALUES(status)";
        $st = $this->db->prepare($sql);
        $st->execute([':e'=>$eventId, ':u'=>$userId, ':s'=>$status]);
        
    }

    public function attendees(int $eventId): array {
        $st = $this->db->prepare(
            "SELECT ea.event_id, ea.user_id, ea.status, ea.checked_in_at,
                    u.username
             FROM event_attendees ea
             JOIN users u ON u.id = ea.user_id
             WHERE ea.event_id=?
             ORDER BY (TRIM(LOWER(ea.status))='going') DESC, u.username ASC"
        );
        $st->execute([$eventId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkin(int $eventId, int $userId): void {
        $st = $this->db->prepare(
            "UPDATE event_attendees
             SET checked_in_at = IF(checked_in_at IS NULL, NOW(), checked_in_at)
             WHERE event_id=? AND user_id=?"
        );
        $st->execute([$eventId,$userId]);
    }

   
    public function countGoing(int $eventId): int {
        $st = $this->db->prepare(
            "SELECT SUM(CASE WHEN TRIM(LOWER(status))='going' THEN 1 ELSE 0 END)
             FROM event_attendees
             WHERE event_id=?"
        );
        $st->execute([$eventId]);
        return (int)$st->fetchColumn();
    } 
}
