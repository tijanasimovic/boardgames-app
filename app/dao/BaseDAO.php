<?php
// app/dao/BaseDAO.php
declare(strict_types=1);

class BaseDAO {
    protected PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }
}
