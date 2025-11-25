<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\QuimicosService;
use App\Domain\DTO\QuimicosDTO;
use App\Shared\Validation\Validator;

function onGetQuimicos() {
    try {
        $quimicosService = new QuimicosService();

        $quimicos = $quimicosService->onGetQuimicos();

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

function onGetCelulasAreas() {
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

function onGetUmbs() {
    try {
        $quimicosService = new QuimicosService();

        $umbs = $quimicosService->onGetUmbs();

        if ($umbs) {
            return $umbs;
        } else {
            throw new Exception("No se encontraron umbs.");
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onPostDeleteQuimico(array $data){
    try {
        $id_quimico = $data['id'] ?? null;

        if ($id_quimico === null) {
            throw new Exception("Error al procesar el Id del quimico.");
        }

        $quimicosService = new QuimicosService();
        $delete_quimico = $quimicosService->deleteQuimico($id_quimico);

        if (!$delete_quimico) {
            throw new Exception("No se pudo eliminar el quimico.");
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
            case 'approve':
                $response = onPostAprobarAdministrador($data);
                break;
            case 'update':
                $response = onPostUpdatePermisosAdministrador($data);
                break;
            case 'delete_quimico':
                $response = onPostDeleteQuimico($data);
                break;
            default:
                throw new Exception("Acción no permitida.");
                break;
        }
    } elseif ($requestMethod === 'GET') {
        $action = $_GET['action'] ?? null;

        switch ($action) {
            case 'onGet_quimicos':
                $response = onGetQuimicos();
                break;
            case 'onGet_celulasAreas':
                $response = onGetCelulasAreas();
                break;
            case 'onGet_umbs':
                $response = onGetUmbs();
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