<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\LoginService;
use App\Domain\DTO\AdministradoresDTO;
use App\Infrastructure\Repository\AdministradoresRepository;
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
    if (isset($data['password_administrador'])) {
        $data['password_administrador'] = password_hash($data['password_administrador'], PASSWORD_DEFAULT);
    }
    $administradorDTO = new AdministradoresDTO(...$data);

    Validator::validateDTO($administradorDTO);

    $loginService = new LoginService();

    $validar_email_registrado = $loginService->validar_email_registrado($administradorDTO->correo_hwi_administrador);

    if ($validar_email_registrado) {
        $status = true;
        throw new Exception();
    } else {
        $guardar_administrador = $loginService->guardar_administrador($administradorDTO);
        if (!$guardar_administrador) {
            throw new Exception('Error al registrar, intente nuevamente.');
        }

        $email_superAdmin = 'ricardo.rojas@hacebwhirlpool.com';
        $asunto = 'Nuevo Registro Administrador';
        $titulo = 'Nuevo Administrador Registrado';

        $nombreCompleto = trim(($administradorDTO->nombre_administrador ?? '') . ' ' . ($administradorDTO->apellidos_administrador ?? ''));
        $cedula = $administradorDTO->cedula_administrador ? number_format($administradorDTO->cedula_administrador, 0, '', '.') : 'No registrada';
        $correo = htmlspecialchars($administradorDTO->correo_hwi_administrador ?? '', ENT_QUOTES, 'UTF-8');

        $contenido = '
            <p style="margin-top: 5px; margin-bottom: 18px;">Se ha registrado un nuevo administrador en el sistema de <strong>Gestión Ambiental - Químicos</strong> y requiere verificación para su activación.</p>
            
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px 20px; margin-bottom: 18px;">
                <p style="margin: 6px 0; font-size: 14px;"><strong>Nombre:</strong> ' . htmlspecialchars($nombreCompleto ?: 'N/A', ENT_QUOTES, 'UTF-8') . '</p>
                <p style="margin: 6px 0; font-size: 14px;"><strong>Cédula:</strong> ' . $cedula . '</p>
                <p style="margin: 6px 0; font-size: 14px;"><strong>Correo HWI:</strong> ' . $correo . '</p>
                <p style="margin: 6px 0; font-size: 14px;"><strong>Estado:</strong> <span style="display: inline-block; background-color: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">Pendiente de Aprobación</span></p>
            </div>
            
            <p style="font-size: 13px; color: #64748b; margin-top: 15px;">Puedes proceder con la validación y asignación de permisos desde el módulo de <em>Administradores</em> en el sistema.</p>
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
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'status' => $status
    ]);
}
