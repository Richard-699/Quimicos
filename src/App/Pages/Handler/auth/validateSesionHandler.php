<?php
session_start();
if (!isset($_SESSION['sidebarinactive'])) {
    $_SESSION['sidebarinactive'] = true;
}

if (!isset($_SESSION['administrador'])) {
    header('Location: /Quimicos/src/App/Pages/View/auth/login.php');
    exit;
}

?>