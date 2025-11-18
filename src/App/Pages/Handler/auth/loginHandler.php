<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\LoginService;
use App\Domain\DTO\AdministradoresDTO;
use App\Shared\Validation\Validator;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $administradoresDTO = new AdministradoresDTO(
        id_administrador: null,
        cedula_administrador: null,
        nombre_administrador: null,
        apellidos_administrador: null,
        correo_hwi_administrador: $_POST['correo_hwi_administrador'] ?? '',
        password_administrador: $_POST['password_administrador'] ?? '',
        password_is_temporal: null,
        estado_administrador: null,
        type: 'login'
    );

    Validator::validateDTO($administradoresDTO);

    $loginService = new LoginService();
    
    $administradorLogin = $loginService->login($administradoresDTO);

    $approved = false;
    $is_temporal = false;

    if($administradorLogin){
        if($administradorLogin->permisosAdministradoresDTO == null){
            throw new Exception("No tiene permisos en el sistema.");
        }
        if($administradorLogin->password_is_temporal == 1){
            $is_temporal = true;
        }
        if($administradorLogin->estado_administrador == 1){
            $approved = true;
        }
        session_start();
        $_SESSION['administrador'] = $administradorLogin;
        $_SESSION['sidebarinactive'] = true;
    }
    
    echo json_encode([
        'success' => true,
        'approved' => $approved,
        'is_temporal' => $is_temporal
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
