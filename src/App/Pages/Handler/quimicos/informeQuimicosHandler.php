<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\InformeQuimicosService;

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Bogota');

try {
    $action = $_GET['action'] ?? null;
    $service = new InformeQuimicosService();
    $response = ['success' => false, 'message' => 'Acción no válida'];

    switch ($action) {
        case 'onGet_filtros':
            $response = $service->getFiltros();
            break;
        case 'onGet_dashboard_data':
            $response = $service->getDashboardData($_GET);
            break;
        default:
            $response = ['success' => false, 'message' => "Acción desconocida: '{$action}'"];
    }
} catch (\Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();
