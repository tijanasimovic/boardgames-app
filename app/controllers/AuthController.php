<?php
// app/controllers/AuthController.php
declare(strict_types=1);

namespace App\Controllers;

use PDO;
require_once __DIR__ . '/../dao/UserDAO.php';

class AuthController {
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    // /?page=login
    public function login(): void {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = (string)($_POST['password'] ?? '');

            if ($username === '' || $password === '') {
                $error = 'Unesite korisničko ime i lozinku.';
            } else {
                $dao = new \UserDAO($this->pdo);
                $u = $dao->findByUsername($username);

                if ($u && $password === $u['password']) {
                    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
                    $_SESSION['user'] = [
                        'id'       => $u['id'],
                        'username' => $u['username'],
                        'email'    => $u['email'],
                        'role'     => $u['role']
                    ];
                    header('Location: ?page=home'); exit;
                } else {
                    $error = 'Pogrešno korisničko ime ili lozinka.';
                }
            }
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    // /?page=register
    public function register(): void {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');

            if (strlen($username) < 3 || strlen($password) < 3) {
                $error = 'Korisničko ime i lozinka moraju imati bar 3 znaka.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Unesite ispravan email.';
            } else {
                $dao = new \UserDAO($this->pdo);

                if ($dao->findByUsernameOrEmail($username, $email)) {
                    $error = 'Korisničko ime ili email je već zauzet.';
                } else {
                    $id = $dao->create($username, $email, $password);

                    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
                    $_SESSION['user'] = [
                        'id'       => $id,
                        'username' => $username,
                        'email'    => $email,
                        'role'     => 'user'
                    ];
                    header('Location: ?page=home'); exit;
                }
            }
        }

        include __DIR__ . '/../views/auth/register.php';
    }

    // /?page=logout
    public function logout(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        session_unset(); session_destroy();
        header('Location: ?page=home'); exit;
    }
}
