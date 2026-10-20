<?php

namespace App\Application\Service;

use App\Infrastructure\Database\Connection;
use App\Infrastructure\Repository\InformeQuimicosRepository;
use Exception;
use PDO;

class InformeQuimicosService
{
    private PDO $db;
    private InformeQuimicosRepository $repository;

    public function __construct()
    {
        $this->db = (new Connection())->dbQuimicosHwi;
        $this->repository = new InformeQuimicosRepository($this->db);
    }

    public function getFiltros(): array
    {
        try {
            $filtros = $this->repository->getFiltros();
            return array_merge(['success' => true], $filtros);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getDashboardData(array $params): array
    {
        try {
            $kpis = $this->repository->getKpisGenerales($params);
            $costoTotalGlobal = (float)$kpis['costo_total'];

            $topFinanciero = $this->repository->getTopQuimicosImpacto($params);
            foreach ($topFinanciero as &$c) {
                $c['costo_total'] = (float)$c['costo_total'];
                $c['porcentaje_impacto'] = $costoTotalGlobal > 0 ? round(($c['costo_total'] / $costoTotalGlobal) * 100, 1) : 0;
            }

            $porCelula = $this->repository->getImpactoPorCelula($params);
            foreach ($porCelula as &$cel) {
                $cel['costo_total'] = (float)$cel['costo_total'];
                $cel['porcentaje_impacto'] = $costoTotalGlobal > 0 ? round(($cel['costo_total'] / $costoTotalGlobal) * 100, 1) : 0;
            }

            $mayorMovimiento = $this->repository->getQuimicosMayorMovimiento($params);
            $mensual = $this->repository->getEvolucionMensual($params);

            $detalle = $this->repository->getDetalleSolicitudes($params);
            foreach ($detalle as &$det) {
                $det['costo_total'] = (float)$det['costo_total'];
                $det['porcentaje_impacto'] = $costoTotalGlobal > 0 ? round(($det['costo_total'] / $costoTotalGlobal) * 100, 1) : 0;
            }

            return [
                'success'           => true,
                'kpis'              => $kpis,
                'top_financiero'    => $topFinanciero,
                'por_celula'        => $porCelula,
                'mayor_movimiento'  => $mayorMovimiento,
                'mensual'           => $mensual,
                'detalle'           => $detalle
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
