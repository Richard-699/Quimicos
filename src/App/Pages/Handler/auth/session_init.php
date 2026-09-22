<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';


if (session_status() === PHP_SESSION_NONE) {
    $session_path = realpath(__DIR__ . '/../../../../../sessions');

    if ($session_path !== false && !is_dir($session_path)) {
        @mkdir($session_path, 0700, true);
    }

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
