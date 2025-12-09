<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\QuimicosService;
use App\Application\Service\SolicitudesConsumoService;
use App\Domain\DTO\SolicitudesConsumoDTO;
use App\Shared\Util\Utilidades;
use App\Shared\Validation\Validator;

function onPostSaveSolicitud(array $data)
{
    try {
        $form = $data['form'] ?? [];
        $fecha_solicitud_consumo = date("Y-m-d");
        $cedula_solicitante = isset($form['cedula_solicitante']) && $form['cedula_solicitante'] !== ''
            ? (int)$form['cedula_solicitante']
            : null;
        $nombres_solicitante_consumo = isset($form['nombres_solicitante_consumo']) ? ucfirst(strtolower($form['nombres_solicitante_consumo'])) : null;
        $apellidos_solicitante_consumo = isset($form['apellidos_solicitante_consumo']) ? ucfirst(strtolower($form['apellidos_solicitante_consumo'])) : null;
        $cantidad_solicitud_consumo = $form['cantidad_solicitud_consumo'] === '' ? null : (float) $form['cantidad_solicitud_consumo'];
        $id_estado = 3; //Pendiente

        $solicitudesConsumoDTO = new SolicitudesConsumoDTO(
            id_solicitud_consumo: null,
            fecha_solicitud_consumo: $fecha_solicitud_consumo ?? null,
            id_celula_area_solicitud_consumo: $form['id_celula_area_solicitud_consumo'] ?? null,
            id_quimico_solicitud_consumo: $form['id_quimico_solicitud_consumo'] ?? null,
            cantidad_solicitud_consumo: $cantidad_solicitud_consumo,
            cedula_solicitante: $cedula_solicitante,
            nombres_solicitante_consumo: $nombres_solicitante_consumo,
            apellidos_solicitante_consumo: $apellidos_solicitante_consumo,
            id_estado_solicitud_quimico: $id_estado
        );

        Validator::validateSolicitudesConsumoDTO($solicitudesConsumoDTO);

        $solicitudesConsumoService = new SolicitudesConsumoService();
        $saveSolicitudesQuimicos = $solicitudesConsumoService->saveSolicitudQuimico($solicitudesConsumoDTO);

        if (!$saveSolicitudesQuimicos) {
            throw new Exception("No se pudo registrar la solicitud.");
        }

        $email_superAdmin = 'ricardo.rojas@hacebwhirlpool.com';
        $asunto = 'Solicitud Químico';
        $titulo = 'Nueva Solicitud de Químico Registrada';
        $contenido = '
            <p style="margin-top: 10px;">Se ha registrado una solicitud de un químico, puedes proceder con la validación para aprobación.</p>
        ';

        $utilidades = new Utilidades();
        $enviarCorreo = $utilidades->enviarCorreo($email_superAdmin, $asunto, $titulo, $contenido);

        if (!$enviarCorreo) {
            throw new Exception('Error al enviar el correo.');
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

function onGetQuimicos($data)
{
    try {
        $id = $data['id'];
        $quimicosService = new QuimicosService();

        $quimicos = $quimicosService->onGetQuimicos_By__Id_Celula($id);

        if ($quimicos) {
            return $quimicos;
        } else {
            throw new Exception("No se encontraron quimicos.");
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onGetCelulasAreas()
{
    try {
        $quimicosService = new QuimicosService();

        $celulas = $quimicosService->onGetCelulas();

        if ($celulas) {
            return $celulas;
        } else {
            throw new Exception("No se encontraron células.");
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
            case 'save_solicitud':
                $response = onPostSaveSolicitud($data);
                break;
            default:
                throw new Exception("Acción no permitida.");
                break;
        }
    } elseif ($requestMethod === 'GET') {
        $action = $_GET['action'] ?? null;

        switch ($action) {
            case 'onGet_quimicos':
                $response = onGetQuimicos($_GET);
                break;
            case 'onGet_celulasAreas':
                $response = onGetCelulasAreas();
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
