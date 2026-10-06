<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\IndicadorConsumoAguaService;
use App\Application\Service\ConsumoAguaService;
use App\Domain\DTO\ConsumoAguaDTO;

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Bogota');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$service = new IndicadorConsumoAguaService();

try {
    if ($method === 'GET') {
        $action = $_GET['action'] ?? null;
        switch ($action) {
            case 'onGet_celulas':
                $response = $service->getCelulas();
                break;
            case 'onGet_tanques':
                $response = $service->getTanques();
                break;
            case 'onGet_anios':
                $response = $service->getAnios();
                break;
            case 'onGet_dashboard_data':
                $response = $service->getDashboardData($_GET);
                break;
            case 'onGet_proyeccion_ml':
                $response = $service->getProyeccionML($_GET);
                break;
            default:
                throw new \Exception("Acción no permitida: '$action'");
        }
    } elseif ($method === 'POST') {
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new \Exception("JSON inválido.");
        }

        $action = $data['action'] ?? null;
        switch ($action) {
            case 'update_consumo_agua':
                $form = $data['form'] ?? [];
                $id_consumo = isset($form['id_consumo_agua']) ? (int)$form['id_consumo_agua'] : null;
                $final      = isset($form['consumo_final_agua']) ? (float)$form['consumo_final_agua'] : null;

                if (!$id_consumo) throw new \Exception("ID de consumo es requerido.");
                if ($final === null) throw new \Exception("Consumo final es requerido.");

                $consumoService = new ConsumoAguaService();
                $dto = new ConsumoAguaDTO(
                    id_consumo_agua: $id_consumo,
                    consumo_final_agua: $final
                );
                $consumoService->updateConsumoAgua($dto);

                $response = ['success' => true, 'message' => 'Consumo actualizado correctamente.'];
                break;
            default:
                throw new \Exception("Acción POST no permitida.");
        }
    } else {
        throw new \Exception("Método no permitido.");
    }
} catch (\Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();
