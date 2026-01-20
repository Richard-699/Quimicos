<?php
// *****************************************************************
// ARCHIVO DE INICIALIZACIÓN COMÚN (Debe ser la PRIMERA inclusión)
// 1. Carga el autoloader de Composer (para deserializar AdministradoresDTO)
// 2. Configura e inicia la sesión
// 3. Define las variables globales ($administrador, $permisos, etc.)
// *****************************************************************

// Ruta del autoloader (Asegúrate de que la ruta sea correcta desde este archivo)
require_once __DIR__ . '/../../../../../vendor/autoload.php';

// ----------------------------------------------------------------------------------
// 1. Lógica de Sesión y Redirección (Extraída de validateSesionHandler.php)

if (session_status() === PHP_SESSION_NONE) {
    // La ruta es: /src/App/Pages/Handler/auth/session_init.php
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
    header('Location: /inventario/src/App/Pages/View/auth/login.php');
    exit;
}

// ----------------------------------------------------------------------------------
// 2. Definición de Variables (Para las Vistas y el Header)

$administrador = $_SESSION['administrador'];

// 3. Inclusión de Utilidades (Spinner)
include(__DIR__ . '/../../../Shared/Util/spinner.php'); 

// Ya que este archivo está diseñado para ejecutarse antes que CUALQUIER HTML, 
// podemos remover el validateSesionHandler.php y el spinner.php de las vistas.

?>
