<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\SolicitudesConsumoService;
use App\Shared\Util\Utilidades;
use App\Shared\Validation\Validator;

function onGetSolicitudes()
{
    try {
        $solicitudesConsumoService = new SolicitudesConsumoService();
        $solicitudes = $solicitudesConsumoService->onGetSolicitudes();

        if ($solicitudes) {
            return $solicitudes;
        } else {
            throw new Exception("No se encontraron solicitudes.");
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onPostUpdateEstadoSolicitud(array $data)
{
    try {
        $id_solicitud_consumo = $data['id'] ?? null;
        $id_estado = $data['id_estado'] ?? null;
        $cantidad_solicitud = $data['cantidad_solicitud'] ?? null;
        $id_quimico = $data['id_quimico'] ?? null;

        if ($id_solicitud_consumo === null) {
            throw new Exception("Error al procesar el Id de la solicitud.");
        }
        if ($id_estado === null) {
            throw new Exception("Error al procesar el Id del estado.");
        }
        if ($cantidad_solicitud === null && $id_estado == 1) {
            throw new Exception("Error al procesar la cantidad de la solicitud.");
        }
        if ($id_quimico === null && $id_estado == 1) {
            throw new Exception("Error al procesar el id del químico.");
        }

        $solicitudesConsumoService = new SolicitudesConsumoService();
        $update_estado_solicitud = $solicitudesConsumoService->updateEstadoSolicitud($id_solicitud_consumo, $id_estado, $cantidad_solicitud, $id_quimico);

        if (!$update_estado_solicitud) {
            throw new Exception("No se pudo actualizar el estado de la solicitud.");
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
            case 'update_estado_solicitud':
                $response = onPostUpdateEstadoSolicitud($data);
                break;
            default:
                throw new Exception("Acción no permitida.");
                break;
        }
    } elseif ($requestMethod === 'GET') {
        $action = $_GET['action'] ?? null;

        switch ($action) {
            case 'onGet_solicitudes':
                $response = onGetSolicitudes();
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
