<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\LoginService;
use App\Domain\DTO\AdministradoresDTO;
use App\Infrastructure\Repository\AdministradoresRepository;
use App\Infrastructure\Repository\PermisosAdministradoresRepository;
use App\Infrastructure\Repository\PermisosRepository;
use App\Shared\Validation\Validator;
use App\Shared\Util\Utilidades;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $data = $_POST;
    $status = false;

    foreach ($data as $key => $value) {
        $data[$key] = $value === '' ? null : $value;
    }

    $data['id_administrador'] = Utilidades::generarGUID();
    $data['password_is_temporal'] = 0;
    $data['estado_administrador'] = 3;
    $data['type'] = 'register';
    if(isset($data['password_administrador'])){
        $data['password_administrador'] = password_hash($data['password_administrador'], PASSWORD_DEFAULT);
    }
    $administradorDTO = new AdministradoresDTO(...$data);

    Validator::validateDTO($administradorDTO);

    $loginService = new LoginService();
    
    $validar_email_registrado = $loginService->validar_email_registrado($administradorDTO->correo_hwi_administrador);

    if($validar_email_registrado){
        $status = true;
        throw new Exception();
    }else{
        $guardar_administrador = $loginService->guardar_administrador($administradorDTO);
        if(!$guardar_administrador){
            throw new Exception('Error al registrar, intente nuevamente.');
        }

        $email_superAdmin = 'ricardo.rojas@hacebwhirlpool.com';
        $asunto = 'Nuevo Registro Administrador';
        $titulo = 'Nuevo Administrador Registrado';
        $contenido = '
            <p style="margin-top: 10px;">Se ha registrado un nuevo administrador en el sistema de inventario, puedes proceder con la verificación.</p>
        ';

        $utilidades = new Utilidades();
        $enviarCorreo = $utilidades->enviarCorreo($email_superAdmin, $asunto, $titulo, $contenido);

        if (!$enviarCorreo) {
            throw new Exception('Error al enviar el correo.');
        }
    }

    echo json_encode([
        'success' => true
    ]);

}catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'status' => $status
    ]);
}