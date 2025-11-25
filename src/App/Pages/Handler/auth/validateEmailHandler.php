<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\LoginService;
use App\Domain\DTO\AdministradoresDTO;
use App\Infrastructure\Repository\AdministradoresRepository;
use App\Infrastructure\Repository\PermisosAdministradoresRepository;
use App\Infrastructure\Repository\PermisosRepository;
use App\Shared\Util\Utilidades;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    $correo_hwi_administrador = $_POST['correo_hwi_administrador'] ?? '';

    $loginService = new LoginService();

    $validar_email_registrado = $loginService->validar_email_registrado($correo_hwi_administrador);

    if (!$validar_email_registrado) {
        throw new Exception('No se encuentra registrado en el sistema.');
    }

    $utilidades = new Utilidades();
    $password_temp = $utilidades->generarPasswordTemporal();

    if (!$password_temp || strlen($password_temp) < 8) {
        throw new Exception('Error al generar la contraseña temporal.');
    }

    $passwordHash = password_hash($password_temp, PASSWORD_DEFAULT);
    $istemporal = 1;

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
        throw new Exception('Error al restablecer la contraseña.');
    }

    $email = $correo_hwi_administrador;
    $asunto = 'Contraseña Restablecida';
    $titulo = 'Contraseña Restablecida';
    $contenido = '
        <p style="margin-top: 10px;">La siguiente es la contraseña temporal para acceder al Sistema Novedades de Nómina</p>
        <p><span style="font-weight: bold;">Contraseña temporal:</span> ' . $password_temp . '</p>
    ';

    $enviarCorreo = $utilidades->enviarCorreo($email, $asunto, $titulo, $contenido);

    if (!$enviarCorreo) {
        throw new Exception('No se pudo enviar el correo.');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
