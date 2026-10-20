<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\LoginService;
use App\Domain\DTO\AdministradoresDTO;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    $session_dir = __DIR__ . '/../../../../../sessions';
    if (!is_dir($session_dir)) {
        @mkdir($session_dir, 0777, true);
    }
    $session_path = realpath($session_dir);
    if ($session_path !== false && is_dir($session_path) && is_writable($session_path)) {
        ini_set('session.save_path', $session_path);
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $correo_hwi_administrador = trim($_POST['correo_hwi_administrador'] ?? '');
    if (empty($correo_hwi_administrador) && isset($_SESSION['administrador'])) {
        $adm = $_SESSION['administrador'];
        $correo_hwi_administrador = is_object($adm) ? ($adm->correo_hwi_administrador ?? '') : ($adm['correo_hwi_administrador'] ?? '');
    }

    if (empty($correo_hwi_administrador)) {
        throw new Exception('No se pudo identificar la cuenta de usuario. Inicie sesión nuevamente.');
    }

    $password_administrador = trim($_POST['password_administrador'] ?? '');
    if (empty($password_administrador)) {
        throw new Exception('La contraseña no puede estar vacía.');
    }

    if (strlen($password_administrador) < 6) {
        throw new Exception('La contraseña debe tener al menos 6 caracteres.');
    }

    $loginService = new LoginService();

    $passwordHash = password_hash($password_administrador, PASSWORD_DEFAULT);
    $istemporal = 0;

    $administradoresDTO = new AdministradoresDTO(
        id_administrador: null,
        cedula_administrador: null,
        nombre_administrador: null,
        apellidos_administrador: null,
        correo_hwi_administrador: $correo_hwi_administrador,
        password_administrador: $passwordHash,
        password_is_temporal: $istemporal,
        estado_administrador: null,
        type: null
    );

    $update_password = $loginService->actualizar_password($administradoresDTO);

    if (!$update_password) {
        throw new Exception('Error al cambiar la contraseña.');
    }

    if (isset($_SESSION['administrador'])) {
        if (is_object($_SESSION['administrador'])) {
            $_SESSION['administrador']->password_is_temporal = 0;
        } elseif (is_array($_SESSION['administrador'])) {
            $_SESSION['administrador']['password_is_temporal'] = 0;
        }
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
