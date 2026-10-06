<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Application\Service\ProyeccionesService;

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Bogota');

$service = new ProyeccionesService();
$response = ['success' => false, 'message' => 'Acción no válida'];

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $action = $_GET['action'] ?? null;
        switch ($action) {
            case 'debug_python':
                $out1 = shell_exec('which python; which python3; python --version 2>&1; python3 --version 2>&1');
                $out2 = shell_exec('python3 -c "import sys; print(sys.executable)" 2>&1');
                $out3 = shell_exec('find /home/customer -name "pandas" -type d 2>/dev/null');
                $out4 = shell_exec('find /home/customer -name "activate" 2>/dev/null');
                echo json_encode([
                    'binaries' => $out1,
                    'py3_bin' => $out2,
                    'pandas_loc' => $out3,
                    'venvs' => $out4
                ]);
                exit;
            case 'onGet_filtros':
                $response = $service->getFiltros();
                break;
            case 'onGet_proyeccion':
                $quimico       = $_GET['quimico'] ?? '';
                $celula        = $_GET['celula'] ?? 'Todas';
                $verSiguiente  = filter_var($_GET['ver_anio_siguiente'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $response = $service->getProyeccion($quimico, $celula, $verSiguiente);
                break;
            default:
                throw new Exception("Acción desconocida: '{$action}'");
        }
    } elseif ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? [];
        $action = $data['action'] ?? null;

        switch ($action) {
            case 'onPost_chat':
                $prompt    = $data['prompt'] ?? '';
                $quimico   = $data['quimico'] ?? '';
                $celula    = $data['celula'] ?? 'Todas';
                $historial = $data['historial'] ?? [];
                $response = $service->chat($prompt, $quimico, $celula, $historial);
                break;
            default:
                throw new Exception("Acción POST no permitida: '{$action}'");
        }
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();
