<?php

namespace App\Application\Service;

use Exception;

class ProyeccionesService
{
    private string $scriptPath;

    public function __construct()
    {
        $this->scriptPath = realpath(__DIR__ . '/../../../../analytics/service/proyecciones_runner.py');
    }

    private function runPython(string $action, array $args = []): array
    {
        if (!$this->scriptPath || !file_exists($this->scriptPath)) {
            return ['success' => false, 'message' => 'Script de análisis Python no encontrado.'];
        }

        $cmdArgs = ['python', escapeshellarg($this->scriptPath), escapeshellarg($action)];
        foreach ($args as $arg) {
            $cmdArgs[] = escapeshellarg($arg);
        }

        $command = implode(' ', $cmdArgs) . ' 2>&1';
        $output = shell_exec($command);

        if (!$output) {
            return ['success' => false, 'message' => 'Sin respuesta del motor de análisis Python.'];
        }

        // Buscar el último bloque JSON válido en la salida
        $lines = explode("\n", trim($output));
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if (str_starts_with($line, '{') && str_ends_with($line, '}')) {
                $decoded = json_decode($line, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
        }

        return ['success' => false, 'message' => 'Error al decodificar respuesta JSON: ' . $output];
    }

    public function getFiltros(): array
    {
        return $this->runPython('filtros');
    }

    public function getProyeccion(string $quimico, string $celula = 'Todas', bool $verAnioSiguiente = false): array
    {
        return $this->runPython('proyeccion', [$quimico, $celula, $verAnioSiguiente ? '1' : '0']);
    }

    public function chat(string $prompt, string $quimico, string $celula = 'Todas', array $historial = []): array
    {
        $payload = json_encode([
            'prompt'    => $prompt,
            'quimico'   => $quimico,
            'celula'    => $celula,
            'historial' => $historial
        ], JSON_UNESCAPED_UNICODE);

        $payloadB64 = base64_encode($payload);
        return $this->runPython('chat', [$payloadB64]);
    }
}
