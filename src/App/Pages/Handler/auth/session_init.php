<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';


if (session_status() === PHP_SESSION_NONE) {
    $session_dir = __DIR__ . '/../../../../../sessions';
    if (!is_dir($session_dir)) {
        @mkdir($session_dir, 0777, true);
    }
    $session_path = realpath($session_dir);

    if ($session_path !== false && is_dir($session_path) && is_writable($session_path)) {
        ini_set('session.save_path', $session_path);
    }
    
    session_start();
}

if (!isset($_SESSION['sidebarinactive'])) {
    $_SESSION['sidebarinactive'] = true;
}

if (!isset($_SESSION['administrador'])) {
    header('Location: /Quimicos/src/App/Pages/View/auth/login.php');
    exit;
}

$administrador = $_SESSION['administrador'];

?>
