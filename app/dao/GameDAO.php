<?php
// app/dao/GameDAO.php
declare(strict_types=1);

class GameDAO {
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

 
    public function listForIndex(?string $q=null, array $genreIds=[], ?int $ratingMin=null, ?string $sort=''): array {
        $where  = [];
        $params = [];
        $join   = '';

       
        if ($q) {
            $where[]     = " g.title LIKE :q ";
            $params[':q']= "%".$q."%";
        }

    
        if (!empty($genreIds)) {
            $join .= " JOIN game_genre gg ON gg.game_id = g.id ";
           
            $inPlaceholders = [];
            foreach ($genreIds as $i => $gid) {
                $ph = ":g{$i}";
                $inPlaceholders[] = $ph;
                $params[$ph] = (int)$gid;
            }
            $where[] = " gg.genre_id IN (".implode(',', $inPlaceholders).") ";
        }

        $whereSql = $where ? ("WHERE ".implode(" AND ", $where)) : "";

        $sql = "
            SELECT g.*,
                g.image_path AS image_url,
                COALESCE(ROUND(AVG(r.rating),1),0) AS rating,
                COUNT(r.id) AS reviews_count
            FROM games g
            $join
            LEFT JOIN reviews r ON r.game_id = g.id
            $whereSql
            GROUP BY g.id
        ";

       
        if ($ratingMin) {
            $sql .= " HAVING rating >= :rmin ";
            $params[':rmin'] = (int)$ratingMin;
        }

       
        $orderMap = [
            'rating_desc' => 'rating DESC, reviews_count DESC, g.title ASC',
            'rating_asc'  => 'rating ASC,  reviews_count DESC, g.title ASC',
            'name_asc'    => 'g.title ASC',
            'name_desc'   => 'g.title DESC',
            ''            => 'g.title ASC'
        ];
        $sql .= " ORDER BY " . ($orderMap[$sort ?? ''] ?? $orderMap['']);

        $st = $this->pdo->prepare($sql);

       
        foreach ($params as $k=>$v) {
            $type = (is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
            $st->bindValue($k, $v, $type);
        }

        $st->execute();
        return $st->fetchAll();
    }

    public function all(?string $q=null, ?int $playersMin=null, ?int $playersMax=null, ?int $timeMax=null, ?string $sort=null): array {
        $sql = "SELECT g.*,
                       g.image_path AS image_url,
                       COALESCE(ROUND(AVG(r.rating),1),0) AS rating,
                       COUNT(r.id) AS reviews_count
                FROM games g
                LEFT JOIN reviews r ON r.game_id = g.id
                ";
        $w = []; $params = [];
        if ($q)          { $w[] = "g.title LIKE :q";        $params[':q'] = "%".$q."%"; }
        if ($playersMin) { $w[] = "g.max_players >= :pmin"; $params[':pmin'] = $playersMin; }
        if ($playersMax) { $w[] = "g.min_players <= :pmax"; $params[':pmax'] = $playersMax; }
        if ($timeMax)    { $w[] = "g.play_time <= :tmax";   $params[':tmax'] = $timeMax; }
        if ($w) { $sql .= " WHERE ".implode(" AND ", $w); }

        $sql .= " GROUP BY g.id ";

      
        if     ($sort === 'rating')   $sql .= " ORDER BY rating DESC, reviews_count DESC, g.title ASC";
        elseif ($sort === 'reviews')  $sql .= " ORDER BY reviews_count DESC, rating DESC, g.title ASC";
        elseif ($sort === 'year')     $sql .= " ORDER BY g.year DESC, g.title ASC";
        else                          $sql .= " ORDER BY g.title ASC";

        $st = $this->pdo->prepare($sql);
        foreach ($params as $k=>$v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->execute();
        return $st->fetchAll();
    }

    public function find(int $id): ?array {
        $st = $this->pdo->prepare("
            SELECT g.*,
                   g.image_path AS image_url
            FROM games g
            WHERE g.id=?
        ");
        $st->execute([$id]);
        $g = $st->fetch();
        return $g ?: null;
    }

   
    public function top(string $by='rating', int $limit=10): array {
        $order = ($by === 'comments') ? "reviews_count DESC" : "rating DESC";
        $sql = "SELECT g.*,
                       g.image_path AS image_url,
                       COALESCE(ROUND(AVG(r.rating),1),0) AS rating,
                       COUNT(r.id) AS reviews_count
                FROM games g
                LEFT JOIN reviews r ON r.game_id = g.id
                GROUP BY g.id
                ORDER BY $order, g.title ASC
                LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
    
   
    public function upsert(?int $id, array $data): int {
        $cols = ['title','description','min_players','max_players','play_time','year','image_path'];
        $vals = [$data['title'],$data['description'],$data['min_players'],$data['max_players'],$data['play_time'],$data['year'],$data['image_path'] ?? null];

        if ($id) {
            $sql = "UPDATE games
                    SET title=?, description=?, min_players=?, max_players=?, play_time=?, year=?, image_path=?
                    WHERE id=?";
            $vals[] = $id;
            $this->pdo->prepare($sql)->execute($vals);
            return $id;
        } else {
            $sql = "INSERT INTO games(title, description, min_players, max_players, play_time, year, image_path)
                    VALUES (?,?,?,?,?,?,?)";
            $this->pdo->prepare($sql)->execute($vals);
            return (int)$this->pdo->lastInsertId();
        }
    }

    public function delete(int $id): void {
        $this->pdo->prepare("DELETE FROM games WHERE id = ?")->execute([$id]);
    }

    public function searchPaged(?string $q=null, ?int $playersMin=null, ?int $playersMax=null, ?int $timeMax=null, ?string $sort=null, int $page=1, int $per=10, ?int $genreId=null): array {
        $where = []; $params = [];
        $join = "";
        if ($genreId) {
            $join .= " JOIN game_genre gg ON gg.game_id = g.id ";
            $where[] = " gg.genre_id = :gid ";
            $params[':gid'] = $genreId;
        }
        if ($q)          { $where[] = "g.title LIKE :q";            $params[':q'] = "%".$q."%"; }
        if ($playersMin) { $where[] = "g.max_players >= :pmin";      $params[':pmin'] = $playersMin; }
        if ($playersMax) { $where[] = "g.min_players <= :pmax";      $params[':pmax'] = $playersMax; }
        if ($timeMax)    { $where[] = "g.play_time <= :tmax";        $params[':tmax'] = $timeMax; }
        $whereSql = $where ? ("WHERE ".implode(" AND ", $where)) : "";

        
        $countSql = "SELECT COUNT(DISTINCT g.id) FROM games g $join $whereSql";
        $stc = $this->pdo->prepare($countSql);
        $stc->execute($params);
        $total = (int)$stc->fetchColumn();

       
        $order = "g.title ASC";
        if     ($sort === 'rating')  $order = "rating DESC, reviews_count DESC, g.title ASC";
        elseif ($sort === 'reviews') $order = "reviews_count DESC, rating DESC, g.title ASC";
        elseif ($sort === 'year')    $order = "g.year DESC, g.title ASC";

        $page   = max(1, $page);
        $per    = max(1, $per);
        $offset = max(0, ($page-1) * $per);

        $sql = "SELECT g.*,
                    g.image_path AS image_url,
                    COALESCE(ROUND(AVG(r.rating),1),0) AS rating,
                    COUNT(r.id) AS reviews_count
                FROM games g
                $join
                LEFT JOIN reviews r ON r.game_id = g.id
                $whereSql
                GROUP BY g.id
                ORDER BY $order
                LIMIT :lim OFFSET :off";
        $st = $this->pdo->prepare($sql);
        foreach ($params as $k=>$v) { $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR); }
        $st->bindValue(':lim', $per, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll();

        return ['rows'=>$rows, 'total'=>$total, 'page'=>$page, 'per'=>$per];
    }

    public function genresFor(int $gameId): array {
        $sql = "SELECT g.name
                FROM game_genre gg
                JOIN genres g ON g.id=gg.genre_id
                WHERE gg.game_id=?
                ORDER BY g.name ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([$gameId]);
        return array_column($st->fetchAll(), 'name');
    }

    public function topPaged(string $by='rating', int $page=1, int $per=10): array {
        $order = ($by === 'comments')
            ? "reviews_count DESC, rating DESC, g.title ASC"
            : "rating DESC, reviews_count DESC, g.title ASC";

        $total = (int)$this->pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();

        $page   = max(1, $page);
        $per    = max(1, $per);
        $offset = ($page - 1) * $per;

        $sql = "SELECT g.*,
                    g.image_path AS image_url,
                    COALESCE(ROUND(AVG(r.rating),1),0) AS rating,
                    COUNT(r.id)               AS reviews_count
                FROM games g
                LEFT JOIN reviews r ON r.game_id = g.id
                GROUP BY g.id
                ORDER BY $order
                LIMIT :lim OFFSET :off";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $per, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll();

        return ['rows'=>$rows, 'total'=>$total, 'per'=>$per];
    }

    public function getAllMinimal(): array {
        $st = $this->pdo->query("SELECT id, title FROM games ORDER BY title ASC");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $st = $this->pdo->prepare("SELECT * FROM games WHERE id=?");
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?:null;
}
}