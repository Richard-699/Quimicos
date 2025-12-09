<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\AdministradoresService;
use App\Domain\DTO\AdministradoresDTO;
use App\Shared\Validation\Validator;

function onPostAprobarAdministrador(array $data){
    try {
        $id_administrador = $data['id'] ?? null;

        if ($id_administrador === null) {
            throw new Exception("Error al procesar el Id del administrador.");
        }

        $estado_administrador = 1;

        $administradoresDTO = new AdministradoresDTO(
            id_administrador: $id_administrador,
            cedula_administrador: null,
            nombre_administrador: null,
            apellidos_administrador: null,
            correo_hwi_administrador: null,
            password_administrador: null,
            password_is_temporal: null,
            estado_administrador: $estado_administrador,
            type: null
        );

        $administradoresService = new AdministradoresService();
        $aprobar_administrador = $administradoresService->aprobarAdministrador($administradoresDTO);

        if (!$aprobar_administrador) {
            throw new Exception("No se pudo aprobar el administrador.");
        }

        return [
            'success' => true
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onPostDeleteAdministrador(array $data){
    try {
        $id_administrador = $data['id'] ?? null;

        if ($id_administrador === null) {
            throw new Exception("Error al procesar el Id del administrador.");
        }

        $administradoresService = new AdministradoresService();

        $delete_administrador = $administradoresService->deleteAdministrador($id_administrador);

        if (!$delete_administrador) {
            throw new Exception("No se pudo eliminar el administrador.");
        }

        return [
            'success' => true
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onPostUpdatePermisosAdministrador(array $data){
    try {
        $form = $data['form'] ?? [];

        $administradoresService = new AdministradoresService();

        $administradoresDTO = new AdministradoresDTO(
            id_administrador: $form['id_administrador'],
            cedula_administrador: null,
            nombre_administrador: null,
            apellidos_administrador: null,
            correo_hwi_administrador: null,
            password_administrador: null,
            password_is_temporal: null,
            estado_administrador: null,
            type: null
        );

        $aprobar_administrador = $administradoresService->updatePermisosAdministrador($administradoresDTO);

        if (!$aprobar_administrador) {
            throw new Exception("No se pudo aprobar el administrador.");
        }

        return [
            'success' => true
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onGetAdministradores() {
    try {
        $administradoresService = new AdministradoresService();

        $administradores = $administradoresService->onGetAdministradores();

        if ($administradores) {
            return $administradores;
        } else {
            throw new Exception("No se encontraron administradores.");
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
    if ($requestMethod === 'POST') {
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new Exception("Datos JSON inválidos o mal formados. Asegúrate de enviar un JSON válido.");
        }

        $action = $data['action'] ?? null;

        switch ($action) {
            case 'approve':
                $response = onPostAprobarAdministrador($data);
                break;
            case 'update':
                $response = onPostUpdatePermisosAdministrador($data);
                break;
            case 'delete_administrador':
                $response = onPostDeleteAdministrador($data);
                break;
            default:
                throw new Exception("Acción no permitida.");
                break;
        }
    } elseif ($requestMethod === 'GET') {
        $action = $_GET['action'] ?? null;

        switch ($action) {
            case 'onGet_administradores':
                $response = onGetAdministradores();
                break;
            default:
                throw new Exception("Acción no permitida.");
                break;
        }
    } else {
        throw new Exception("Método no permitido.");
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => "Un error interno ocurrió: " . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
exit();

?>