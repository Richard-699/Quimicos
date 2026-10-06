<?php

namespace App\Application\Service;

use App\Infrastructure\Database\Connection;
use App\Infrastructure\Repository\IndicadorConsumoAguaRepository;
use DateTime;
use Exception;
use PDO;

class IndicadorConsumoAguaService
{
    private PDO $db;
    private IndicadorConsumoAguaRepository $repository;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? (new Connection())->dbQuimicosHwi;
        $this->repository = new IndicadorConsumoAguaRepository($this->db);
    }

    public function getCelulas(): array
    {
        try {
            $rows = $this->repository->getCelulas();
            $rows[] = [
                'id_celulas_areas' => 'humano',
                'nombre_celula'    => 'Consumo Humano'
            ];
            return $rows;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTanques(): array
    {
        try {
            return $this->repository->getTanques();
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAnios(): array
    {
        return $this->repository->getAnios();
    }

    public function buildWhere(array $params): array
    {
        $id_celula = isset($params['id_celula']) && $params['id_celula'] !== '' ? $params['id_celula'] : null;
        $id_tanque = isset($params['id_tanque']) && $params['id_tanque'] !== '' ? (int)$params['id_tanque'] : null;
        $anio      = isset($params['anio'])      && $params['anio']      !== '' ? (int)$params['anio']      : null;
        $mes       = isset($params['mes'])       && $params['mes']       !== '' ? (int)$params['mes']       : null;
        $fecha     = isset($params['fecha'])     && $params['fecha']     !== '' ? $params['fecha']           : null;

        $where = [];
        $binds = [];

        // Excluir Contador Principal (id_tanque = 6) de las sumas diarias regulares para no duplicar datos
        if ($id_tanque) {
            $where[] = 'c.id_tanque_abastecimiento_consumo_agua = :id_tanque';
            $binds[':id_tanque'] = $id_tanque;
        } else {
            $where[] = '(c.id_tanque_abastecimiento_consumo_agua != 6 OR c.id_tanque_abastecimiento_consumo_agua IS NULL)';
        }

        if ($id_celula && $id_celula !== 'humano') {
            $where[] = 'c.id_celula_consumo_agua = :id_celula';
            $binds[':id_celula'] = (int)$id_celula;
        }
        if ($fecha) {
            $where[] = 'DATE(c.fecha_consumo_agua) = :fecha';
            $binds[':fecha'] = $fecha;
        } else {
            if ($anio) {
                $where[] = 'YEAR(c.fecha_consumo_agua) = :anio';
                $binds[':anio'] = $anio;
            }
            if ($mes) {
                $where[] = 'MONTH(c.fecha_consumo_agua) = :mes';
                $binds[':mes'] = $mes;
            }
        }

        return [
            'where'     => $where,
            'binds'     => $binds,
            'whereStr'  => $where ? 'WHERE ' . implode(' AND ', $where) : '',
            'id_celula' => $id_celula,
            'id_tanque' => $id_tanque,
            'anio'      => $anio,
            'mes'       => $mes,
            'fecha'     => $fecha,
        ];
    }

    public function getConsumoHumanoData(array $params): array
    {
        $anio = isset($params['anio']) && $params['anio'] !== '' ? (int)$params['anio'] : null;
        $mes  = isset($params['mes'])  && $params['mes']  !== '' ? (int)$params['mes']  : null;

        $ppalRows = $this->repository->getConsumoPrincipalMensual();
        $cellsRows = $this->repository->getConsumoCelulasMensualSinLAP();

        $mapCells = [];
        foreach ($cellsRows as $cr) {
            $mapCells[$cr['fecha_mes']] = (float)$cr['consumo_cells'];
        }

        $allMonthly = [];
        foreach ($ppalRows as $pr) {
            $f = $pr['fecha_mes'];
            $c_ppal  = (float)$pr['consumo_ppal'];
            $c_cells = $mapCells[$f] ?? 0.0;
            $c_humano = max(0.0, $c_ppal - $c_cells);

            $allMonthly[] = [
                'fecha_mes'      => $f,
                'anio'           => (int)$pr['anio'],
                'mes'            => (int)$pr['mes'],
                'consumo_ppal'   => round($c_ppal, 2),
                'consumo_cells'  => round($c_cells, 2),
                'consumo_humano' => round($c_humano, 2),
            ];
        }

        // Filtrar por año y mes si fue seleccionado en el dashboard
        $filtered = array_filter($allMonthly, function($row) use ($anio, $mes) {
            if ($anio && $row['anio'] !== $anio) return false;
            if ($mes && $row['mes'] !== $mes) return false;
            return true;
        });
        $filtered = array_values($filtered);

        $consumos = array_column($filtered, 'consumo_humano');
        $totalConsumo = array_sum($consumos);
        $totalRegistros = count($filtered);
        $promedioMensual = $totalRegistros > 0 ? $totalConsumo / $totalRegistros : 0;
        $picoMaximo = $totalRegistros > 0 ? max($consumos) : 0;

        $kpi = [
            'total_consumo'   => $totalConsumo,
            'promedio_diario' => $promedioMensual,
            'pico_maximo'     => $picoMaximo,
            'total_registros' => $totalRegistros,
            'total_dias'      => $totalRegistros,
            'total_alertas'   => 0,
            'tope_promedio'   => null,
        ];

        $porMes = array_map(fn($r) => [
            'anio' => $r['anio'],
            'mes'  => $r['mes'],
            'consumo_total' => $r['consumo_humano']
        ], $filtered);

        $serieTemporal = array_map(fn($r) => [
            'fecha' => $r['fecha_mes'],
            'consumo_total' => $r['consumo_humano'],
            'tope_ml' => null
        ], $filtered);

        $porCelula = [
            ['nombre_celula' => 'Consumo Humano', 'consumo_total' => $totalConsumo]
        ];

        $registros = array_map(fn($r) => [
            'id_consumo_agua'      => 'humano_' . $r['fecha_mes'],
            'fecha_consumo_agua'   => $r['fecha_mes'],
            'nombre_celula'        => 'Consumo Humano',
            'nombre_tanque'        => 'Ppal (' . number_format($r['consumo_ppal'], 1) . ' m³) - Células (' . number_format($r['consumo_cells'], 1) . ' m³)',
            'consumo_inicial_agua' => $r['consumo_cells'],
            'consumo_final_agua'   => $r['consumo_ppal'],
            'consumo_neto'         => $r['consumo_humano'],
            'supera_tope'          => null,
            'tope_calculado'       => null,
            'score_anomalia'       => null,
        ], $filtered);

        return [
            'success'        => true,
            'kpi'            => $kpi,
            'registros'      => $registros,
            'por_mes'        => $porMes,
            'por_celula'     => $porCelula,
            'por_tanque'     => [],
            'serie_temporal' => $serieTemporal,
            'alertas'        => [],
        ];
    }

    public function getDashboardData(array $params): array
    {
        try {
            if (isset($params['id_celula']) && $params['id_celula'] === 'humano') {
                return $this->getConsumoHumanoData($params);
            }

            $ctx = $this->buildWhere($params);
            $w   = $ctx['whereStr'];
            $b   = $ctx['binds'];

            $kpi = $this->repository->getKpi($w, $b);
            $registros = $this->repository->getRegistros($w, $b);

            // Consumo por mes (eliminamos filtro de mes/fecha)
            $bMes = array_filter($b, fn($k) => $k !== ':mes' && $k !== ':fecha', ARRAY_FILTER_USE_KEY);
            $wMes = array_filter($ctx['where'], fn($w2) => !str_contains($w2, ':mes') && !str_contains($w2, ':fecha'));
            $whereStrMes = $wMes ? 'WHERE ' . implode(' AND ', array_values($wMes)) : '';
            $porMes = $this->repository->getConsumoPorMes($whereStrMes, $bMes);

            $porCelula = $this->repository->getConsumoPorCelula($w, $b);
            $porTanque = $this->repository->getConsumoPorTanque($w, $b);
            $serieTemporal = $this->repository->getSerieTemporal($w, $b);

            // Ajustar KPIs diarios con total consistencia temporal
            $consumosDiarios = array_map(fn($d) => (float)($d['consumo_total'] ?? 0), $serieTemporal);
            $totalDias       = count($consumosDiarios);
            $totalConsumo    = (float)($kpi['total_consumo'] ?? 0);
            $picoDiario      = $totalDias > 0 ? max($consumosDiarios) : 0;
            $promedioDiario  = $totalDias > 0 ? ($totalConsumo / $totalDias) : 0;

            $kpi['pico_maximo']     = $picoDiario;
            $kpi['promedio_diario'] = $promedioDiario;
            $kpi['total_dias']      = $totalDias;

            // Alertas ML
            $alertaWhere = [];
            $alertaBinds = [];
            if ($ctx['id_celula']) {
                $alertaWhere[] = 'c.id_celula_consumo_agua = :id_celula';
                $alertaBinds[':id_celula'] = $ctx['id_celula'];
            }
            if ($ctx['id_tanque']) {
                $alertaWhere[] = 'c.id_tanque_abastecimiento_consumo_agua = :id_tanque';
                $alertaBinds[':id_tanque'] = $ctx['id_tanque'];
            }
            if ($ctx['anio']) {
                $alertaWhere[] = 'YEAR(c.fecha_consumo_agua) = :anio';
                $alertaBinds[':anio'] = $ctx['anio'];
            }
            if ($ctx['mes']) {
                $alertaWhere[] = 'MONTH(c.fecha_consumo_agua) = :mes';
                $alertaBinds[':mes'] = $ctx['mes'];
            }
            $alertaW = $alertaWhere ? 'AND ' . implode(' AND ', $alertaWhere) : '';
            $alertas = $this->repository->getAlertasML($alertaW, $alertaBinds);

            return [
                'success'        => true,
                'kpi'            => $kpi,
                'registros'      => $registros,
                'por_mes'        => $porMes,
                'por_celula'     => $porCelula,
                'por_tanque'     => $porTanque,
                'serie_temporal' => $serieTemporal,
                'alertas'        => $alertas,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getProyeccionML(array $params): array
    {
        try {
            $id_celula = isset($params['id_celula']) && $params['id_celula'] !== '' ? $params['id_celula'] : null;
            $id_tanque = isset($params['id_tanque']) && $params['id_tanque'] !== '' ? (int)$params['id_tanque'] : null;

            if ($id_celula === 'humano') {
                $ppalRows = $this->repository->getConsumoPrincipalMensual();
                $cellsRows = $this->repository->getConsumoCelulasMensualSinLAP();
                $mapCells = [];
                foreach ($cellsRows as $cr) {
                    $mapCells[$cr['fecha_mes']] = (float)$cr['consumo_cells'];
                }

                $historico = [];
                foreach ($ppalRows as $pr) {
                    $f = $pr['fecha_mes'];
                    $c_ppal  = (float)$pr['consumo_ppal'];
                    $c_cells = $mapCells[$f] ?? 0.0;
                    $c_humano = max(0.0, $c_ppal - $c_cells);
                    $historico[] = [
                        'fecha_mes'        => $f,
                        'consumo_mensual'  => round($c_humano, 2),
                        'tope_mensual'     => null,
                    ];
                }
            } else {
                $where = [];
                $binds = [];
                if ($id_tanque) {
                    $where[] = 'c.id_tanque_abastecimiento_consumo_agua = :id_tanque';
                    $binds[':id_tanque'] = $id_tanque;
                } else {
                    $where[] = '(c.id_tanque_abastecimiento_consumo_agua != 6 OR c.id_tanque_abastecimiento_consumo_agua IS NULL)';
                }
                if ($id_celula) {
                    $where[] = 'c.id_celula_consumo_agua = :id_celula';
                    $binds[':id_celula'] = (int)$id_celula;
                }
                $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
                $historico = $this->repository->getHistoricoMensual($whereStr, $binds);
            }

            if (empty($historico)) {
                return ['success' => true, 'historico' => [], 'proyeccion' => []];
            }

            // Algoritmo Holt-Winters
            $consumosRaw = array_map('floatval', array_column($historico, 'consumo_mensual'));
            $fechas      = array_column($historico, 'fecha_mes');
            $n           = count($consumosRaw);

            $validos = array_filter($consumosRaw, fn($v) => $v > 0);
            $mediana = !empty($validos) ? (array_sum($validos) / count($validos)) : 100.0;
            $consumos = array_map(fn($v) => ($v <= 0 ? $mediana : $v), $consumosRaw);

            $seasonLength = 12;
            $seasonalFactors = array_fill(0, $seasonLength, 1.0);

            if ($n >= $seasonLength) {
                $sumT = 0; $sumY = 0; $sumTY = 0; $sumT2 = 0;
                for ($i = 0; $i < $n; $i++) {
                    $sumT  += $i;
                    $sumY  += $consumos[$i];
                    $sumTY += ($i * $consumos[$i]);
                    $sumT2 += ($i * $i);
                }
                $denom = ($n * $sumT2 - $sumT * $sumT);
                $slope = $denom != 0 ? ($n * $sumTY - $sumT * $sumY) / $denom : 0;
                $intercept = ($sumY - $slope * $sumT) / $n;

                $monthTotals = array_fill(0, $seasonLength, 0.0);
                $monthCounts = array_fill(0, $seasonLength, 0);

                for ($i = 0; $i < $n; $i++) {
                    $m = (int)date('n', strtotime($fechas[$i])) - 1;
                    $trendVal = max($mediana * 0.2, $intercept + $slope * $i);
                    $ratio = $consumos[$i] / $trendVal;
                    $monthTotals[$m] += $ratio;
                    $monthCounts[$m]++;
                }

                for ($m = 0; $m < $seasonLength; $m++) {
                    if ($monthCounts[$m] > 0) {
                        $seasonalFactors[$m] = $monthTotals[$m] / $monthCounts[$m];
                    }
                }

                $meanSeason = array_sum($seasonalFactors) / $seasonLength;
                if ($meanSeason > 0) {
                    for ($m = 0; $m < $seasonLength; $m++) {
                        $seasonalFactors[$m] /= $meanSeason;
                    }
                }
            }

            $alpha = 0.35;
            $beta  = 0.15;
            $gamma = 0.30;
            $phi   = 0.85;

            $m0 = (int)date('n', strtotime($fechas[0])) - 1;
            $level = $consumos[0] / max(0.1, $seasonalFactors[$m0]);
            $trend = ($consumos[$n - 1] - $consumos[0]) / max(1, $n - 1) * 0.5;
            $seasonals = $seasonalFactors;

            for ($i = 0; $i < $n; $i++) {
                $m = (int)date('n', strtotime($fechas[$i])) - 1;
                $val = $consumos[$i];
                $lastLevel = $level;

                $level = $alpha * ($val / max(0.1, $seasonals[$m])) + (1 - $alpha) * ($level + $phi * $trend);
                $trend = $beta * ($level - $lastLevel) + (1 - $beta) * ($phi * $trend);
                $seasonals[$m] = $gamma * ($val / max(0.1, $level)) + (1 - $gamma) * $seasonals[$m];
            }

            $ultimaFecha = new DateTime(end($historico)['fecha_mes']);
            $proyeccion  = [];

            $topesVal  = array_filter(array_column($historico, 'tope_mensual'), fn($v) => $v !== null && $v !== '');
            $topeMedia = !empty($topesVal) ? array_sum($topesVal) / count($topesVal) : null;

            for ($h = 1; $h <= 6; $h++) {
                $fechaProj = clone $ultimaFecha;
                $fechaProj->modify("+{$h} month");
                $targetMonth = (int)$fechaProj->format('n') - 1;

                $dampedSum = 0;
                for ($k = 1; $k <= $h; $k++) {
                    $dampedSum += pow($phi, $k);
                }

                $forecastLevel = $level + $trend * $dampedSum;
                $seasonMult    = $seasonals[$targetMonth] ?? 1.0;
                $forecastVal   = max(0.0, $forecastLevel * $seasonMult);

                $proyeccion[] = [
                    'fecha_mes'          => $fechaProj->format('Y-m-01'),
                    'consumo_proyectado' => round($forecastVal, 2),
                    'tope_estimado'      => $topeMedia !== null ? round($topeMedia, 2) : null,
                ];
            }

            return [
                'success'    => true,
                'historico'  => $historico,
                'proyeccion' => $proyeccion,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
