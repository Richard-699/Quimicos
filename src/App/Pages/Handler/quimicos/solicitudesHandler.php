<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\SolicitudesConsumoService;
use App\Domain\DTO\CadenciaActualDTO;
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

function obtenerCadencias() {
    try {
        $solicitudesConsumoService = new SolicitudesConsumoService();
        $cadencias = $solicitudesConsumoService->obtenerCadencias();

        if ($cadencias) {
            return $cadencias;
        } else {
            throw new Exception("No se encontraron cadencias.");
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function obtenerCadenciaActual() {
    try {
        $solicitudesConsumoService = new SolicitudesConsumoService();
        $cadenciaActual = $solicitudesConsumoService->obtenerCadenciaActual();

        if ($cadenciaActual) {
            return $cadenciaActual;
        } else {
            throw new Exception("No se encontró la cadencia actual.");
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

function updateCadenciaActual(array $data) {
    try {
        date_default_timezone_set('America/Bogota');
        $fecha_hora_registro_cadencia_actual = date('Y-m-d H:i:s');
        $id_cadencias_cadencia_actual = isset($data['id_cadencias_cadencia_actual']) ? $data['id_cadencias_cadencia_actual'] : null;
        $session_path = realpath(__DIR__ . '/../../../../../sessions');

        if ($session_path && is_writable($session_path)) {
            ini_set('session.save_path', $session_path);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id_administrador_cadencia_actual = $_SESSION['administrador']->id_administrador;

        $cadenciaActualDTO = new CadenciaActualDTO(
            id_cadencia_actual: null,
            fecha_hora_registro_cadencia_actual: $fecha_hora_registro_cadencia_actual,
            id_cadencias_cadencia_actual: $id_cadencias_cadencia_actual,
            cadencia: null,
            id_administrador_cadencia_actual: $id_administrador_cadencia_actual 
        );

        Validator::validateDTO($cadenciaActualDTO);

        $solicitudesConsumoService = new SolicitudesConsumoService();
        if(!$solicitudesConsumoService->guardarCadenciaActual($cadenciaActualDTO)){
            throw new Exception("No se pudo guardar la cadencia actual.");
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
            case 'updateCadenciaActual':
                $response = updateCadenciaActual($data['form'] ?? []);
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
            case 'obtenerCadencias':
                $response = obtenerCadencias();
                break;
            case 'obtenerCadenciaActual':
                $response = obtenerCadenciaActual();
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
