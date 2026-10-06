<?php

namespace App\Application\Service;

use App\Infrastructure\Database\Connection;
use PDO;
use DateTime;
use Exception;

class ProyeccionesService
{
    private PDO $db;
    private ?string $celulasTableName = null;

    public function __construct()
    {
        $this->db = (new Connection())->dbQuimicosHwi;
    }

    /**
     * Detecta el nombre de la tabla de células/áreas disponible en la BD.
     */
    private function getCelulasTableName(): string
    {
        if ($this->celulasTableName !== null) {
            return $this->celulasTableName;
        }

        try {
            $this->db->query("SELECT 1 FROM gestion_ambiental_hwi_celulas_areas LIMIT 1");
            return $this->celulasTableName = 'gestion_ambiental_hwi_celulas_areas';
        } catch (\Throwable $e) {
            return $this->celulasTableName = 'quimicos_hwi_celulas_areas';
        }
    }

    /**
     * Obtiene listas de químicos y células para los selectores del frontend.
     */
    public function getFiltros(): array
    {
        try {
            $celulasTable = $this->getCelulasTableName();

            // 1. Químicos disponibles en catálogo o con consumos registrados
            $stmtQ = $this->db->query("
                SELECT DISTINCT TRIM(descripcion_quimico) AS quimico
                FROM quimicos_hwi_quimicos
                WHERE descripcion_quimico IS NOT NULL AND TRIM(descripcion_quimico) != ''
                ORDER BY quimico ASC
            ");
            $quimicos = $stmtQ->fetchAll(PDO::FETCH_COLUMN) ?: [];

            // 2. Células / Áreas disponibles
            $celulas = ['Todas'];
            try {
                $stmtC = $this->db->query("
                    SELECT DISTINCT TRIM(nombre_celula) AS celula
                    FROM {$celulasTable}
                    WHERE nombre_celula IS NOT NULL AND TRIM(nombre_celula) != ''
                    ORDER BY celula ASC
                ");
                $cList = $stmtC->fetchAll(PDO::FETCH_COLUMN) ?: [];
                $celulas = array_values(array_unique(array_merge(['Todas'], $cList)));
            } catch (\Throwable $e) {
                // Si la tabla de células falla, se mantiene 'Todas'
            }

            if (empty($quimicos)) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron químicos en el catálogo de proyecciones.'
                ];
            }

            return [
                'success' => true,
                'quimicos' => $quimicos,
                'celulas' => $celulas
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener filtros: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcula la proyección de demanda usando el modelo de suavizado exponencial Holt-Winters con amortiguación.
     */
    private function generarPronosticoDemanda(array $consumosValidos, int $numMeses): array
    {
        if ($numMeses <= 0) {
            return [];
        }

        $nPuntos = count($consumosValidos);
        if ($nPuntos === 0) {
            return array_fill(0, $numMeses, 0.1);
        }
        if ($nPuntos === 1) {
            return array_fill(0, $numMeses, round((float)$consumosValidos[0], 2));
        }

        $mediaHistorica = array_sum($consumosValidos) / $nPuntos;
        $picoMaximo = max($consumosValidos);

        $alpha = 0.4;
        $nivel = (float)$consumosValidos[0];
        $tendencia = 0.0;

        for ($i = 1; $i < $nPuntos; $i++) {
            $prevNivel = $nivel;
            $val = (float)$consumosValidos[$i];
            $nivel = $alpha * $val + (1.0 - $alpha) * ($nivel + $tendencia);
            $tendencia = 0.2 * ($nivel - $prevNivel) + 0.8 * $tendencia;
        }

        $predsFinales = [];
        $nivelProyectado = $nivel;
        $factorRetornoMedia = 0.15;
        $cotaSuperior = max($picoMaximo * 1.1, $mediaHistorica * 1.3);

        for ($h = 0; $h < $numMeses; $h++) {
            $tendencia *= 0.5;
            $nivelProyectado = (1.0 - $factorRetornoMedia) * ($nivelProyectado + $tendencia) + ($factorRetornoMedia * $mediaHistorica);
            $valFinal = min(max(0.0, $nivelProyectado), $cotaSuperior);
            $predsFinales[] = round($valFinal, 2);
        }

        return $predsFinales;
    }

    /**
     * Obtiene proyección y KPIs para un químico y célula especificados.
     */
    public function getProyeccion(string $quimico, string $celula = 'Todas', bool $verAnioSiguiente = false): array
    {
        try {
            $quimicoClean = trim($quimico);
            if ($quimicoClean === '') {
                return ['success' => false, 'message' => 'Debe especificar un químico.'];
            }

            // 1. Datos logísticos y maestro del químico
            $stmtMaster = $this->db->prepare("
                SELECT 
                    q.id_quimico,
                    TRIM(q.descripcion_quimico) AS descripcion_quimico,
                    COALESCE(u.descripcion_umb, 'u.') AS umb,
                    COALESCE(q.cantidad_disponible_quimico, 0) AS stock_actual,
                    COALESCE(q.tope_minimo_quimico, 0) AS stock_minimo,
                    COALESCE(q.cantidad_maxima_almacenamiento_quimico, 0) AS stock_maximo,
                    COALESCE(q.tiempo_entrega_minimo_quimico, 0) AS lead_time_min,
                    COALESCE(q.tiempo_entrega_maximo_quimico, 0) AS lead_time_max,
                    COALESCE(q.precio_quimico, 0) AS precio_quimico
                FROM quimicos_hwi_quimicos AS q
                LEFT JOIN quimicos_hwi_umbs AS u ON q.id_umb_quimico = u.id_umb
                WHERE TRIM(q.descripcion_quimico) = :quimico
                LIMIT 1
            ");
            $stmtMaster->execute([':quimico' => $quimicoClean]);
            $masterInfo = $stmtMaster->fetch(PDO::FETCH_ASSOC);

            if (!$masterInfo) {
                return [
                    'success' => true,
                    'sin_datos' => true,
                    'quimico' => $quimico,
                    'celula' => $celula,
                    'mensaje' => "El químico '{$quimico}' no existe en el catálogo."
                ];
            }

            $umb = $masterInfo['umb'] ?: 'u.';
            $precioActual = (float)$masterInfo['precio_quimico'];

            // Consultar último precio en log de precios si existe
            try {
                $stmtPrecio = $this->db->prepare("
                    SELECT lp.precio_quimico
                    FROM quimicos_hwi_logs_precios lp
                    INNER JOIN quimicos_hwi_quimicos q ON lp.id_quimico_log_precio = q.id_quimico
                    WHERE TRIM(q.descripcion_quimico) = :quimico
                    ORDER BY lp.fecha_log_precio DESC
                    LIMIT 1
                ");
                $stmtPrecio->execute([':quimico' => $quimicoClean]);
                $ultimoPrecioLog = $stmtPrecio->fetchColumn();
                if ($ultimoPrecioLog !== false && (float)$ultimoPrecioLog > 0) {
                    $precioActual = (float)$ultimoPrecioLog;
                }
            } catch (\Throwable $e) {
                // Se conserva $precioActual del maestro
            }

            // Consultar precios históricos mensuales si existen
            $preciosPorMes = [];
            try {
                $stmtPreciosMes = $this->db->prepare("
                    SELECT 
                        DATE_FORMAT(lp.fecha_log_precio, '%Y-%m-01') AS fecha,
                        AVG(lp.precio_quimico) AS precio_mes
                    FROM quimicos_hwi_logs_precios lp
                    INNER JOIN quimicos_hwi_quimicos q ON lp.id_quimico_log_precio = q.id_quimico
                    WHERE TRIM(q.descripcion_quimico) = :quimico
                    GROUP BY DATE_FORMAT(lp.fecha_log_precio, '%Y-%m-01')
                ");
                $stmtPreciosMes->execute([':quimico' => $quimicoClean]);
                $rowsPrecios = $stmtPreciosMes->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rowsPrecios as $rp) {
                    $preciosPorMes[$rp['fecha']] = (float)$rp['precio_mes'];
                }
            } catch (\Throwable $e) {
                $preciosPorMes = [];
            }

            // 2. Histórico mensual de consumos (solicitudes aprobadas)
            $celulasTable = $this->getCelulasTableName();
            $params = [':quimico' => $quimicoClean];
            $celulaSql = '';
            if ($celula !== 'Todas' && trim($celula) !== '') {
                $celulaSql = "AND TRIM(c.nombre_celula) = :celula";
                $params[':celula'] = trim($celula);
            }

            $sqlConsumos = "
                SELECT 
                    DATE_FORMAT(sc.fecha_solicitud_consumo, '%Y-%m-01') AS fecha,
                    SUM(sc.cantidad_solicitud_consumo) AS consumo_kg
                FROM quimicos_hwi_solicitudes_consumo AS sc
                INNER JOIN quimicos_hwi_quimicos AS q 
                    ON sc.id_quimico_solicitud_consumo = q.id_quimico
                LEFT JOIN {$celulasTable} AS c 
                    ON sc.id_celula_area_solicitud_consumo = c.id_celulas_areas
                WHERE sc.id_estado_solicitud_quimico = 1
                  AND TRIM(q.descripcion_quimico) = :quimico
                  {$celulaSql}
                GROUP BY DATE_FORMAT(sc.fecha_solicitud_consumo, '%Y-%m-01')
                ORDER BY fecha ASC
            ";
            $stmtConsumos = $this->db->prepare($sqlConsumos);
            $stmtConsumos->execute($params);
            $rawConsumos = $stmtConsumos->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rawConsumos)) {
                return [
                    'success' => true,
                    'sin_datos' => true,
                    'quimico' => $quimico,
                    'celula' => $celula,
                    'mensaje' => "No hay registros de consumo para {$quimico} en la célula {$celula}"
                ];
            }

            $consumosMap = [];
            foreach ($rawConsumos as $rc) {
                $consumosMap[$rc['fecha']] = (float)$rc['consumo_kg'];
            }

            // 3. Delimitar fechas y rango histórico
            $hoy = new DateTime('now');
            $primerDiaMesActual = new DateTime(date('Y-m-01'));
            $ultimoMesCerrado = (clone $primerDiaMesActual)->modify('-1 month');

            $fechaMinStr = $rawConsumos[0]['fecha'];
            $fechaMin = new DateTime($fechaMinStr);
            if ($fechaMin > $primerDiaMesActual) {
                $fechaMin = clone $ultimoMesCerrado;
            }

            // Construir serie histórica continua mes a mes
            $serieHistorico = [];
            $consumosTraining = [];
            $cur = clone $fechaMin;

            while ($cur <= $primerDiaMesActual) {
                $fStr = $cur->format('Y-m-01');
                $cKg = $consumosMap[$fStr] ?? 0.0;
                $pVal = $preciosPorMes[$fStr] ?? $precioActual;

                if ($cur <= $ultimoMesCerrado) {
                    $consumosTraining[] = $cKg;
                }

                $serieHistorico[] = [
                    'fecha' => $fStr,
                    'consumo_kg' => round($cKg, 2),
                    'precio_quimico' => (float)$pVal,
                    'gasto_total' => round($cKg * $pVal, 2)
                ];

                $cur->modify('+1 month');
            }

            // Si no hay meses cerrados anteriores, usar toda la serie histórica disponible
            if (empty($consumosTraining)) {
                $consumosTraining = array_column($serieHistorico, 'consumo_kg');
            }

            // 4. Generar rango proyectado
            $anioActual = (int)date('Y');
            $finAnioProy = $verAnioSiguiente ? ($anioActual + 1) : $anioActual;
            $finProy = new DateTime("{$finAnioProy}-12-01");

            $projDates = [];
            $curP = clone $primerDiaMesActual;
            while ($curP <= $finProy) {
                $projDates[] = $curP->format('Y-m-01');
                $curP->modify('+1 month');
            }

            $valoresPred = $this->generarPronosticoDemanda($consumosTraining, count($projDates));

            $serieProyectado = [];
            foreach ($projDates as $idx => $fStr) {
                $val = $valoresPred[$idx] ?? 0.0;
                $serieProyectado[] = [
                    'fecha' => $fStr,
                    'consumo_kg' => round((float)$val, 2),
                    'precio_quimico' => (float)$precioActual,
                    'gasto_total' => round((float)$val * $precioActual, 2)
                ];
            }

            // 5. Métricas y KPIs
            $consumosPositivos = array_filter(array_column($serieHistorico, 'consumo_kg'), fn($v) => $v > 0);
            if (!empty($consumosPositivos)) {
                $cProm = array_sum($consumosPositivos) / count($consumosPositivos);
            } else {
                $todosC = array_column($serieHistorico, 'consumo_kg');
                $cProm = !empty($todosC) ? (array_sum($todosC) / count($todosC)) : 0.0;
            }

            $cPred = !empty($valoresPred) ? (float)$valoresPred[0] : (float)$cProm;

            $stockAct = (float)$masterInfo['stock_actual'];
            $stockMin = (float)$masterInfo['stock_minimo'];
            $stockMax = (float)$masterInfo['stock_maximo'];
            $ltMin    = (int)$masterInfo['lead_time_min'];
            $ltMax    = (int)$masterInfo['lead_time_max'];

            $varPct = $cProm > 0 ? (($cPred - $cProm) / $cProm * 100.0) : 0.0;
            $sugPedir = $stockMax > 0 ? max(0.0, $stockMax - $stockAct) : max(0.0, ($cPred * 2) - $stockAct);

            $mesesEs = [
                1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
                5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
                9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
            ];

            if ($stockAct <= $stockMin) {
                $momentoReorden = "⚠️ Stock crítico - Realizar pedido de inmediato";
            } else {
                $cobertura = $cPred > 0 ? ($stockAct / $cPred) : 12;
                $sumMeses = max(1, (int)$cobertura);
                $fReorden = (clone $hoy)->modify("+{$sumMeses} months");
                $mNum = (int)$fReorden->format('n');
                $nomMes = $mesesEs[$mNum] ?? $fReorden->format('F');
                $momentoReorden = "Comprar antes de {$nomMes} de " . $fReorden->format('Y');
            }

            // 6. Ingresos recientes de inventario
            $ingresos = [];
            try {
                $stmtIng = $this->db->prepare("
                    SELECT 
                        DATE_FORMAT(i.fecha_ingreso_inventario, '%Y-%m-%d') AS fecha,
                        i.cantidad_ingreso_inventario AS cantidad
                    FROM quimicos_hwi_logs_ingreso_inventario AS i
                    INNER JOIN quimicos_hwi_quimicos AS q 
                        ON i.id_quimico_ingreso_inventario = q.id_quimico
                    WHERE TRIM(q.descripcion_quimico) = :quimico
                    ORDER BY i.fecha_ingreso_inventario DESC
                    LIMIT 10
                ");
                $stmtIng->execute([':quimico' => $quimicoClean]);
                $ingRows = $stmtIng->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($ingRows as $ir) {
                    $ingresos[] = [
                        'fecha' => $ir['fecha'],
                        'cantidad' => (float)$ir['cantidad']
                    ];
                }
            } catch (\Throwable $e) {
                $ingresos = [];
            }

            return [
                'success' => true,
                'sin_datos' => false,
                'quimico' => $quimicoClean,
                'celula' => $celula,
                'umb' => $umb,
                'kpis' => [
                    'precio' => (float)$precioActual,
                    'c_prom' => round($cProm, 2),
                    'c_pred' => round($cPred, 2),
                    'var' => round($varPct, 1),
                    'stock_act' => round($stockAct, 2),
                    'stock_min' => round($stockMin, 2),
                    'stock_max' => round($stockMax, 2),
                    'lt_min' => $ltMin,
                    'lt_max' => $ltMax,
                    'sug_pedir' => round($sugPedir, 2),
                    'momento_reorden' => $momentoReorden
                ],
                'historico' => $serieHistorico,
                'proyectado' => $serieProyectado,
                'ingresos' => $ingresos
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error al calcular proyección: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Chatbot logístico con soporte para Groq Cloud API y respuesta local inteligente basada en los datos reales.
     */
    public function chat(string $prompt, string $quimico, string $celula = 'Todas', array $historial = []): array
    {
        $proy = $this->getProyeccion($quimico, $celula, false);

        if (!empty($proy['sin_datos']) || empty($proy['kpis'])) {
            return [
                'success' => true,
                'respuesta' => "No se registran consumos ni datos suficientes para **{$quimico}** en la célula **{$celula}**."
            ];
        }

        $k = $proy['kpis'];
        $umb = $proy['umb'];

        // Intentar llamada directa a API de Groq si hay API Key configurada
        $apiKey = getenv('GROQ_API_KEY') ?: ($_ENV['GROQ_API_KEY'] ?? null);
        if ($apiKey) {
            $contexto = "PRODUCTO: {$quimico} | CÉLULA: {$celula} | STOCK DISPONIBLE: " . number_format($k['stock_act'], 2) . " {$umb} (Mínimo: {$k['stock_min']}, Máximo: {$k['stock_max']})\n"
                      . "ALERTA COMPRA: {$k['momento_reorden']} | CANTIDAD SUGERIDA A PEDIR: " . number_format($k['sug_pedir'], 2) . " {$umb}\n"
                      . "TIEMPO DE ENTREGA: {$k['lt_min']} a {$k['lt_max']} días hábiles\n"
                      . "PRECIO: $" . number_format($k['precio'], 0) . " COP por {$umb}\n"
                      . "PROMEDIO CONSUMO: " . number_format($k['c_prom'], 2) . " {$umb} | PROYECCIÓN PRÓXIMO MES: " . number_format($k['c_pred'], 2) . " {$umb} ({$k['var']}%)\n";

            $messages = [
                [
                    'role' => 'system',
                    'content' => "Eres el asistente logístico experto en inventario de la empresa HWI. REGLA OBLIGATORIA: Responde SIEMPRE Y ÚNICAMENTE en ESPAÑOL. Responde de forma concisa, clara y basada en este contexto:\n" . $contexto
                ]
            ];

            foreach ($historial as $h) {
                if (!empty($h['role']) && !empty($h['content'])) {
                    $messages[] = [
                        'role' => $h['role'] === 'user' ? 'user' : 'assistant',
                        'content' => (string)$h['content']
                    ];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $prompt];

            $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => $messages,
                    'temperature' => 0.3,
                    'max_tokens' => 500
                ]),
                CURLOPT_TIMEOUT => 4
            ]);

            $rawRes = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $rawRes) {
                $decoded = json_decode($rawRes, true);
                $content = $decoded['choices'][0]['message']['content'] ?? null;
                if ($content) {
                    return ['success' => true, 'respuesta' => trim($content)];
                }
            }
        }

        // Fallback inteligente local basado en los KPIs calculados
        $pLower = mb_strtolower($prompt, 'UTF-8');

        if (preg_match('/(stock|cuanto hay|cuánto hay|disponible|cantidad)/i', $pLower)) {
            $resp = "Para **{$quimico}**, actualmente contamos con **" . number_format($k['stock_act'], 1) . " {$umb}** disponibles en inventario (Tope mínimo: " . number_format($k['stock_min'], 0) . " {$umb}, Máximo: " . number_format($k['stock_max'], 0) . " {$umb}).";
        } elseif (preg_match('/(compra|pedir|reorden|cuanto pido|cuánto pido|alerta)/i', $pLower)) {
            $resp = "El estado logístico indica: **{$k['momento_reorden']}**. La cantidad sugerida a pedir es de **" . number_format($k['sug_pedir'], 1) . " {$umb}** y el tiempo de entrega del proveedor es de **{$k['lt_min']} a {$k['lt_max']} días hábiles**.";
        } elseif (preg_match('/(precio|costo|vale|valor)/i', $pLower)) {
            $resp = "El precio unitario actual registrado de **{$quimico}** es de **$" . number_format($k['precio'], 0) . " COP** por {$umb}.";
        } elseif (preg_match('/(proyeccion|proyección|proximo mes|próximo mes|consumo|pronostico|pronóstico)/i', $pLower)) {
            $sign = $k['var'] > 0 ? '+' : '';
            $resp = "El consumo promedio histórico es de **" . number_format($k['c_prom'], 1) . " {$umb}** y la proyección calculada para el próximo mes es de **" . number_format($k['c_pred'], 1) . " {$umb}** ({$sign}{$k['var']}% vs promedio).";
        } else {
            $resp = "Resumen logístico de **{$quimico}** ({$celula}):\n\n"
                  . "• **Stock Disponible:** " . number_format($k['stock_act'], 1) . " {$umb}\n"
                  . "• **Consumo Estimado Próx. Mes:** " . number_format($k['c_pred'], 1) . " {$umb}\n"
                  . "• **Estado de Reorden:** {$k['momento_reorden']}\n"
                  . "• **Tiempo de Entrega:** {$k['lt_min']} a {$k['lt_max']} días hábiles\n\n"
                  . "¿Deseas conocer más detalles sobre compras, proyecciones o precios?";
        }

        return ['success' => true, 'respuesta' => $resp];
    }
}
