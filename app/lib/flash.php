<?php
// app/lib/flash.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function flash_set(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type'=>$type, 'msg'=>$msg];
}

function flash_get_all(): array {
    $all = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $all;
}
