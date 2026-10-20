<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\ConsumoAguaService;
use App\Application\Service\TanquesAbastecimientoAguaService;
use App\Domain\DTO\ConsumoAguaDTO;
use App\Shared\Validation\Validator;

function onGetUltimosConsumos(array $data)
{
    try {
        $id_celula = $data['id_celula'] ?? null;
        if (!$id_celula) {
            throw new Exception("ID de célula es requerido.");
        }

        $service = new ConsumoAguaService();
        $consumos = $service->onGetUltimosConsumos((int)$id_celula);

        $resultArray = [];
        foreach ($consumos as $c) {
            $resultArray[] = [
                'id_consumo_agua' => $c->id_consumo_agua,
                'fecha_consumo_agua' => $c->fecha_consumo_agua,
                'nombre_tanque' => $c->nombre_tanque ?: 'No Aplica',
                'consumo_inicial_agua' => $c->consumo_inicial_agua,
                'consumo_final_agua' => $c->consumo_final_agua,
                'id_tanque_abastecimiento_consumo_agua' => $c->id_tanque_abastecimiento_consumo_agua
            ];
        }

        return $resultArray;
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onGetTanques()
{
    try {
        $service = new TanquesAbastecimientoAguaService();
        return $service->onGetTanques();
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onPostSaveConsumoAgua(array $data)
{
    try {
        $form = $data['form'] ?? [];
        
        $id_celula = isset($form['id_celula_consumo_agua']) && $form['id_celula_consumo_agua'] !== '' ? (int)$form['id_celula_consumo_agua'] : null;
        $id_tanque = isset($form['id_tanque_abastecimiento_consumo_agua']) && $form['id_tanque_abastecimiento_consumo_agua'] !== '' ? (int)$form['id_tanque_abastecimiento_consumo_agua'] : null;
        $fecha     = isset($form['fecha_consumo_agua']) && !empty($form['fecha_consumo_agua']) ? trim($form['fecha_consumo_agua']) : null;
        
        $dto = new ConsumoAguaDTO(
            fecha_consumo_agua: $fecha,
            id_celula_consumo_agua: $id_celula,
            id_tanque_abastecimiento_consumo_agua: $id_tanque,
            consumo_inicial_agua: isset($form['consumo_inicial_agua']) && $form['consumo_inicial_agua'] !== '' ? (float)$form['consumo_inicial_agua'] : null,
            consumo_final_agua: isset($form['consumo_final_agua']) && $form['consumo_final_agua'] !== '' ? (float)$form['consumo_final_agua'] : null
        );

        Validator::validateDTO($dto);

        $service = new ConsumoAguaService();
        $service->saveConsumoAgua($dto);

        return ['success' => true];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function onPostUpdateConsumoAgua(array $data)
{
    try {
        $form = $data['form'] ?? [];
        
        $id_consumo = isset($form['id_consumo_agua']) && $form['id_consumo_agua'] !== '' ? (int)$form['id_consumo_agua'] : null;
        $final      = isset($form['consumo_final_agua']) && $form['consumo_final_agua'] !== '' ? (float)$form['consumo_final_agua'] : null;
        
        if (!$id_consumo) {
            throw new Exception("ID del registro es obligatorio.");
        }
        if ($final === null) {
            throw new Exception("El consumo final es obligatorio.");
        }

        $dto = new ConsumoAguaDTO(
            id_consumo_agua: $id_consumo,
            consumo_final_agua: $final
        );

        $service = new ConsumoAguaService();
        $service->updateConsumoAgua($dto);

        return ['success' => true, 'message' => 'Consumo actualizado correctamente.'];
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
            throw new Exception("Datos JSON inválidos.");
        }

        $action = $data['action'] ?? null;
        switch ($action) {
            case 'save_consumo_agua':
                $response = onPostSaveConsumoAgua($data);
                break;
            case 'update_consumo_agua':
                $response = onPostUpdateConsumoAgua($data);
                break;
            default:
                throw new Exception("Acción no permitida.");
        }
    } elseif ($requestMethod === 'GET') {
        $action = $_GET['action'] ?? null;
        switch ($action) {
            case 'onGet_consumos_agua':
                $response = onGetUltimosConsumos($_GET);
                break;
            case 'onGet_tanques':
                $response = onGetTanques();
                break;
            default:
                throw new Exception("Acción no permitida.");
        }
    } else {
        throw new Exception("Método no permitido.");
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => "Error interno: " . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
