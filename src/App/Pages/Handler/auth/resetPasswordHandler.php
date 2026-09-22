<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\LoginService;
use App\Domain\DTO\AdministradoresDTO;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    $correo_hwi_administrador = $_POST['correo_hwi_administrador'] ?? '';
    $password_administrador = $_POST['password_administrador'] ?? '';

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

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
