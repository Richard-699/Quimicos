<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\QuimicosService;
use App\Domain\DTO\QuimicosDTO;
use App\Shared\Util\Utilidades;
use App\Shared\Validation\Validator;

function onGetQuimicos()
{
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

function onGetPeligrosidades()
{
    try {
        $quimicosService = new QuimicosService();

        $peligrosidades = $quimicosService->onGetPeligrosidades();

        if ($peligrosidades) {
            return $peligrosidades;
        } else {
            throw new Exception("No se encontraron peligrosidades.");
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onGetCelulasAreasSelected($data)
{
    try {
        $id = $data['id'] ?? null;

        if ($id === null) {
            throw new Exception("Error al procesar el Id del químico.");
        }
        $quimicosService = new QuimicosService();

        $quimicosCelulasAreas = $quimicosService->onGetQuimicosCelulasAreas_By__Id_Quimico($id);

        if ($quimicosCelulasAreas) {
            return $quimicosCelulasAreas;
        } else {
            throw new Exception("No se encontraron células para este químico.");
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onGetQuimicoSelected($data)
{
    try {
        $id = $data['id'] ?? null;

        if ($id === null) {
            throw new Exception("Error al procesar el Id del químico.");
        }
        $quimicosService = new QuimicosService();

        $quimico = $quimicosService->onGetQuimicos_By__Id($id);

        if ($quimico) {
            return $quimico;
        } else {
            throw new Exception("No se encontró este químico.");
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onGetUmbs()
{
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

function onPostDeleteQuimico(array $data)
{
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

function onPostSaveQuimicos(array $data)
{
    try {
        $form = $data['form'] ?? [];
        $descripcion_quimico = isset($form['descripcion_quimico']) ? mb_strtoupper($form['descripcion_quimico'], 'UTF-8') : null;
        $fabricante_quimico = isset($form['fabricante_quimico']) ? mb_strtoupper($form['fabricante_quimico'], 'UTF-8') : null;
        $uso_quimico = isset($form['uso_quimico']) ? $form['uso_quimico'] : null;
        $celulas_areas_quimicos = $form['celulas_areas_quimicos'] ?? [];
        $cantidad_disponible = $form['cantidad_disponible_quimico'] === '' ? null : (float) $form['cantidad_disponible_quimico'];
        $cantidad_maxima = $form['cantidad_maxima_retiro_quimico'] === '' ? null : (float) $form['cantidad_maxima_retiro_quimico'];
        $tope_minimo = $form['tope_minimo_quimico'] === '' ? null : (float) $form['tope_minimo_quimico'];
        $precio = $form['precio_quimico'] === '' ? null : (float) $form['precio_quimico'];
        $id_estado = 4; //Activo

        if (!is_array($celulas_areas_quimicos)) {
            $celulas_areas_quimicos = [$celulas_areas_quimicos];
        }

        $celulas_areas_quimicosIds = [];
        foreach ($celulas_areas_quimicos as $celulas_areas_quimicos_id) {
            $celulas_areas_quimicosIds[] = (int)$celulas_areas_quimicos_id;
        }

        $id_quimico = Utilidades::generarGUID();

        $quimicosDTO = new QuimicosDTO(
            id_quimico: $id_quimico,
            descripcion_quimico: $descripcion_quimico ?? null,
            fabricante_quimico: $fabricante_quimico ?? null,
            id_peligrosidad_quimico: $form['id_peligrosidad_quimico'] ?? null,
            uso_quimico: $uso_quimico,
            id_umb_quimico: $form['id_umb_quimico'] ?? null,
            cantidad_disponible_quimico: $cantidad_disponible,
            cantidad_maxima_retiro_quimico: $cantidad_maxima,
            tope_minimo_quimico: $tope_minimo,
            precio_quimico: $precio,
            id_estado_quimico: $id_estado,
            url_etiqueta_emergencia_quimico:$form['url_etiqueta_emergencia_quimico'],
            quimicosCelulasAreasDTO: $celulas_areas_quimicosIds,
        );

        Validator::validateQuimicosDTO($quimicosDTO);

        $quimicosService = new QuimicosService();
        $saveQuimicos = $quimicosService->saveQuimicos($quimicosDTO);

        if (!$saveQuimicos) {
            throw new Exception("No se pudo guardar el químico");
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

function onPostUpdateQuimico(array $data){
    try {
        $form = $data['form'] ?? [];
        $id_quimico = isset($form['id_quimico']) ? (string)$form['id_quimico'] : null;
        $descripcion_quimico = isset($form['descripcion_quimico']) ? mb_strtoupper($form['descripcion_quimico'], 'UTF-8') : null;
        $fabricante_quimico = isset($form['fabricante_quimico']) ? mb_strtoupper($form['fabricante_quimico'], 'UTF-8') : null;
        $uso_quimico = isset($form['uso_quimico']) ? $form['uso_quimico'] : null;
        $celulas_areas_quimicos = $form['celulas_areas_quimicos'] ?? [];
        $cantidad_disponible = $form['cantidad_disponible_quimico'] === '' ? null : (float) $form['cantidad_disponible_quimico'];
        $cantidad_maxima = $form['cantidad_maxima_retiro_quimico'] === '' ? null : (float) $form['cantidad_maxima_retiro_quimico'];
        $tope_minimo = $form['tope_minimo_quimico'] === '' ? null : (float) $form['tope_minimo_quimico'];
        $precio = $form['precio_quimico'] === '' ? null : (float) $form['precio_quimico'];
        $id_estado = 4; //Activo

        if (!is_array($celulas_areas_quimicos)) {
            $celulas_areas_quimicos = [$celulas_areas_quimicos];
        }

        $celulas_areas_quimicosIds = [];
        foreach ($celulas_areas_quimicos as $celulas_areas_quimicos_id) {
            $celulas_areas_quimicosIds[] = (int)$celulas_areas_quimicos_id;
        }

        $quimicosDTO = new QuimicosDTO(
            id_quimico: $id_quimico,
            descripcion_quimico: $descripcion_quimico ?? null,
            fabricante_quimico: $fabricante_quimico ?? null,
            id_peligrosidad_quimico: $form['id_peligrosidad_quimico'] ?? null,
            uso_quimico: $uso_quimico,
            id_umb_quimico: $form['id_umb_quimico'] ?? null,
            cantidad_disponible_quimico: $cantidad_disponible,
            cantidad_maxima_retiro_quimico: $cantidad_maxima,
            tope_minimo_quimico: $tope_minimo,
            precio_quimico: $precio,
            id_estado_quimico: $id_estado,
            url_etiqueta_emergencia_quimico:$form['url_etiqueta_emergencia_quimico'],
            quimicosCelulasAreasDTO: $celulas_areas_quimicosIds,
        );

        Validator::validateQuimicosDTO($quimicosDTO);

        $quimicosService = new QuimicosService();
        $saveQuimicos = $quimicosService->updateQuimicos($quimicosDTO);

        if (!$saveQuimicos) {
            throw new Exception("No se pudo actualizar el químico");
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
            case 'save_quimico':
                $response = onPostSaveQuimicos($data);
                break;
            case 'updateQuimico':
                $response = onPostUpdateQuimico($data);
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
            case 'onGet_celulasAreasSelected':
                $response = onGetCelulasAreasSelected($_GET);
                break;
            case 'onGet_umbs':
                $response = onGetUmbs();
                break;
            case 'onGet_peligrosidades':
                $response = onGetPeligrosidades();
                break;
            case 'onGet_quimicoSelected':
                $response = onGetQuimicoSelected($_GET);
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
