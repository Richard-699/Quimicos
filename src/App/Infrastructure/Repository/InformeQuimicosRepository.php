<?php

namespace App\Infrastructure\Repository;

use PDO;

class InformeQuimicosRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function execQuery(string $sql, array $binds = []): array
    {
        $stmt = $this->db->prepare($sql);
        foreach ($binds as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFiltros(): array
    {
        $anios = $this->execQuery("
            SELECT DISTINCT YEAR(fecha_solicitud_consumo) AS anio
            FROM quimicos_hwi_solicitudes_consumo
            WHERE id_estado_solicitud_quimico = 1
            ORDER BY anio DESC
        ");

        $celulas = $this->execQuery("
            SELECT DISTINCT ca.id_celulas_areas, ca.nombre_celula
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN gestion_ambiental_hwi_celulas_areas ca 
                ON s.id_celula_area_solicitud_consumo = ca.id_celulas_areas
            WHERE s.id_estado_solicitud_quimico = 1
            ORDER BY ca.nombre_celula ASC
        ");

        $quimicos = $this->execQuery("
            SELECT DISTINCT 
                q.id_quimico, 
                q.descripcion_quimico, 
                q.fabricante_quimico,
                CONCAT(TRIM(q.descripcion_quimico), ' - ', TRIM(q.fabricante_quimico)) AS nombre_completo
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN quimicos_hwi_quimicos q 
                ON s.id_quimico_solicitud_consumo = q.id_quimico
            WHERE s.id_estado_solicitud_quimico = 1
            ORDER BY q.descripcion_quimico ASC
        ");

        return [
            'anios'    => array_column($anios, 'anio'),
            'celulas'  => $celulas,
            'quimicos' => $quimicos
        ];
    }

    public function buildWhere(array $params, ?int $forceEstado = null): array
    {
        $anio       = isset($params['anio']) && $params['anio'] !== '' ? (int)$params['anio'] : null;
        $mes        = isset($params['mes']) && $params['mes'] !== '' ? (int)$params['mes'] : null;
        $id_celula  = isset($params['id_celula']) && $params['id_celula'] !== '' ? (int)$params['id_celula'] : null;
        $id_quimico = isset($params['id_quimico']) && $params['id_quimico'] !== '' ? trim($params['id_quimico']) : null;
        $id_estado  = isset($params['id_estado']) && $params['id_estado'] !== '' ? (int)$params['id_estado'] : null;

        $where = [];
        $binds = [];

        // Si se fuerza un estado explícito (ej: 1 para aprobados, 2 para rechazos, 3 para pendientes)
        if ($forceEstado !== null) {
            // Si el usuario filtró por un estado diferente al forzado, la condición no tiene intersección
            if ($id_estado !== null && $id_estado !== $forceEstado) {
                $where[] = '1 = 0';
            } else {
                $where[] = 's.id_estado_solicitud_quimico = :id_estado_force';
                $binds[':id_estado_force'] = $forceEstado;
            }
        } elseif ($id_estado !== null) {
            $where[] = 's.id_estado_solicitud_quimico = :id_estado';
            $binds[':id_estado'] = $id_estado;
        }

        if ($anio) {
            $where[] = 'YEAR(s.fecha_solicitud_consumo) = :anio';
            $binds[':anio'] = $anio;
        }
        if ($mes) {
            $where[] = 'MONTH(s.fecha_solicitud_consumo) = :mes';
            $binds[':mes'] = $mes;
        }
        if ($id_celula) {
            $where[] = 's.id_celula_area_solicitud_consumo = :id_celula';
            $binds[':id_celula'] = $id_celula;
        }
        if ($id_quimico) {
            $where[] = 's.id_quimico_solicitud_consumo = :id_quimico';
            $binds[':id_quimico'] = $id_quimico;
        }

        return [
            'whereStr' => $where ? 'WHERE ' . implode(' AND ', $where) : '',
            'binds'    => $binds
        ];
    }

    public function buildWhereForCharts(array $params): array
    {
        $id_estado = isset($params['id_estado']) && $params['id_estado'] !== '' ? (int)$params['id_estado'] : null;
        // Si el usuario especificó un estado, usamos ese estado.
        // Si no seleccionó ninguno ('Todos los estados'), por defecto los gráficos muestran Aprobados (consumo real = 1).
        $targetEstado = $id_estado !== null ? $id_estado : 1;
        return $this->buildWhere($params, $targetEstado);
    }

    public function getKpisGenerales(array $params): array
    {
        // 1. Solicitudes Aprobadas (Consumo Real)
        $whereAprob = $this->buildWhere($params, 1);
        $sqlAprob = "
            SELECT 
                COALESCE(SUM(s.cantidad_solicitud_consumo), 0) AS cantidad_total,
                COALESCE(SUM(s.cantidad_solicitud_consumo * q.precio_quimico), 0) AS costo_total,
                COUNT(s.id_solicitud_consumo) AS total_solicitudes
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN quimicos_hwi_quimicos q 
                ON s.id_quimico_solicitud_consumo = q.id_quimico
            {$whereAprob['whereStr']}
        ";
        $rowAprob = $this->execQuery($sqlAprob, $whereAprob['binds'])[0] ?? [];

        // 2. Solicitudes Rechazadas (Costos Evitados)
        $whereRech = $this->buildWhere($params, 2);
        $sqlRech = "
            SELECT 
                COALESCE(SUM(s.cantidad_solicitud_consumo), 0) AS cantidad_rechazada,
                COALESCE(SUM(s.cantidad_solicitud_consumo * q.precio_quimico), 0) AS costo_evitado,
                COUNT(s.id_solicitud_consumo) AS total_rechazadas
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN quimicos_hwi_quimicos q 
                ON s.id_quimico_solicitud_consumo = q.id_quimico
            {$whereRech['whereStr']}
        ";
        $rowRech = $this->execQuery($sqlRech, $whereRech['binds'])[0] ?? [];

        // 3. Solicitudes Pendientes (En Evaluación)
        $wherePend = $this->buildWhere($params, 3);
        $sqlPend = "
            SELECT 
                COALESCE(SUM(s.cantidad_solicitud_consumo), 0) AS cantidad_pendiente,
                COALESCE(SUM(s.cantidad_solicitud_consumo * q.precio_quimico), 0) AS costo_pendiente,
                COUNT(s.id_solicitud_consumo) AS total_pendientes
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN quimicos_hwi_quimicos q 
                ON s.id_quimico_solicitud_consumo = q.id_quimico
            {$wherePend['whereStr']}
        ";
        $rowPend = $this->execQuery($sqlPend, $wherePend['binds'])[0] ?? [];

        // 4. Químicos Filtrados y Células según el filtro activo
        $whereFiltered = $this->buildWhereForCharts($params);
        $sqlFiltered = "
            SELECT 
                COUNT(DISTINCT s.id_quimico_solicitud_consumo) AS total_quimicos_filtrados,
                COUNT(DISTINCT s.id_celula_area_solicitud_consumo) AS total_celulas_filtradas
            FROM quimicos_hwi_solicitudes_consumo s
            {$whereFiltered['whereStr']}
        ";
        $rowFiltered = $this->execQuery($sqlFiltered, $whereFiltered['binds'])[0] ?? [];

        // 5. Químicos Registrados en Catálogo Maestro
        $sqlReg = "SELECT COUNT(*) AS total_registrados FROM quimicos_hwi_quimicos WHERE id_estado_quimico = 4";
        $rowReg = $this->execQuery($sqlReg)[0] ?? [];
        $quimicosRegistrados = (int)($rowReg['total_registrados'] ?? 0);
        if ($quimicosRegistrados === 0) {
            $sqlRegFallback = "SELECT COUNT(*) AS total_registrados FROM quimicos_hwi_quimicos";
            $rowReg = $this->execQuery($sqlRegFallback)[0] ?? [];
            $quimicosRegistrados = (int)($rowReg['total_registrados'] ?? 0);
        }

        return [
            'cantidad_total'        => (float)($rowAprob['cantidad_total'] ?? 0),
            'costo_total'           => (float)($rowAprob['costo_total'] ?? 0),
            'total_solicitudes'     => (int)($rowAprob['total_solicitudes'] ?? 0),
            'total_quimicos'        => (int)($rowFiltered['total_quimicos_filtrados'] ?? 0),
            'total_registrados'     => $quimicosRegistrados,
            'total_celulas'         => (int)($rowFiltered['total_celulas_filtradas'] ?? 0),
            'costo_evitado'         => (float)($rowRech['costo_evitado'] ?? 0),
            'total_rechazadas'      => (int)($rowRech['total_rechazadas'] ?? 0),
            'costo_pendiente'       => (float)($rowPend['costo_pendiente'] ?? 0),
            'total_pendientes'      => (int)($rowPend['total_pendientes'] ?? 0)
        ];
    }

    public function getTopQuimicosImpacto(array $params): array
    {
        $where = $this->buildWhereForCharts($params);
        $sql = "
            SELECT 
                q.id_quimico,
                CONCAT(TRIM(q.descripcion_quimico), ' - ', TRIM(q.fabricante_quimico)) AS nombre_quimico,
                TRIM(q.descripcion_quimico) AS descripcion,
                TRIM(q.fabricante_quimico) AS fabricante,
                COALESCE(u.descripcion_umb, 'Unidad') AS umb,
                COALESCE(q.precio_quimico, 0) AS precio_unitario,
                COALESCE(SUM(s.cantidad_solicitud_consumo), 0) AS cantidad_total,
                COALESCE(SUM(s.cantidad_solicitud_consumo * q.precio_quimico), 0) AS costo_total,
                COUNT(s.id_solicitud_consumo) AS total_despachos
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN quimicos_hwi_quimicos q 
                ON s.id_quimico_solicitud_consumo = q.id_quimico
            LEFT JOIN quimicos_hwi_umbs u 
                ON q.id_umb_quimico = u.id_umb
            {$where['whereStr']}
            GROUP BY q.id_quimico, q.descripcion_quimico, q.fabricante_quimico, u.descripcion_umb, q.precio_quimico
            ORDER BY costo_total DESC
            LIMIT 10
        ";
        return $this->execQuery($sql, $where['binds']);
    }

    public function getImpactoPorCelula(array $params): array
    {
        $where = $this->buildWhereForCharts($params);
        $sql = "
            SELECT 
                ca.id_celulas_areas,
                ca.nombre_celula,
                COALESCE(SUM(s.cantidad_solicitud_consumo), 0) AS cantidad_total,
                COALESCE(SUM(s.cantidad_solicitud_consumo * q.precio_quimico), 0) AS costo_total,
                COUNT(s.id_solicitud_consumo) AS total_solicitudes
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN gestion_ambiental_hwi_celulas_areas ca 
                ON s.id_celula_area_solicitud_consumo = ca.id_celulas_areas
            JOIN quimicos_hwi_quimicos q 
                ON s.id_quimico_solicitud_consumo = q.id_quimico
            {$where['whereStr']}
            GROUP BY ca.id_celulas_areas, ca.nombre_celula
            ORDER BY costo_total DESC
            LIMIT 10
        ";
        return $this->execQuery($sql, $where['binds']);
    }

    public function getQuimicosMayorMovimiento(array $params): array
    {
        $where = $this->buildWhereForCharts($params);
        $sql = "
            SELECT 
                q.id_quimico,
                CONCAT(TRIM(q.descripcion_quimico), ' - ', TRIM(q.fabricante_quimico)) AS nombre_quimico,
                TRIM(q.descripcion_quimico) AS descripcion,
                TRIM(q.fabricante_quimico) AS fabricante,
                COALESCE(u.descripcion_umb, 'Unidad') AS umb,
                COALESCE(q.precio_quimico, 0) AS precio_unitario,
                COALESCE(SUM(s.cantidad_solicitud_consumo), 0) AS cantidad_total,
                COALESCE(SUM(s.cantidad_solicitud_consumo * q.precio_quimico), 0) AS costo_total,
                COUNT(s.id_solicitud_consumo) AS total_despachos
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN quimicos_hwi_quimicos q 
                ON s.id_quimico_solicitud_consumo = q.id_quimico
            LEFT JOIN quimicos_hwi_umbs u 
                ON q.id_umb_quimico = u.id_umb
            {$where['whereStr']}
            GROUP BY q.id_quimico, q.descripcion_quimico, q.fabricante_quimico, u.descripcion_umb, q.precio_quimico
            ORDER BY cantidad_total DESC
            LIMIT 10
        ";
        return $this->execQuery($sql, $where['binds']);
    }

    public function getEvolucionMensual(array $params): array
    {
        $paramsSinMes = $params;
        unset($paramsSinMes['mes']);
        $where = $this->buildWhereForCharts($paramsSinMes);

        $sql = "
            SELECT 
                DATE_FORMAT(s.fecha_solicitud_consumo, '%Y-%m') AS periodo,
                YEAR(s.fecha_solicitud_consumo) AS anio,
                MONTH(s.fecha_solicitud_consumo) AS mes,
                COALESCE(SUM(s.cantidad_solicitud_consumo), 0) AS cantidad_total,
                COALESCE(SUM(s.cantidad_solicitud_consumo * q.precio_quimico), 0) AS costo_total,
                COUNT(s.id_solicitud_consumo) AS total_solicitudes
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN quimicos_hwi_quimicos q 
                ON s.id_quimico_solicitud_consumo = q.id_quimico
            {$where['whereStr']}
            GROUP BY periodo, anio, mes
            ORDER BY periodo ASC
        ";
        return $this->execQuery($sql, $where['binds']);
    }

    /**
     * Trae cada solicitud individual completa para ver el historial exacto
     */
    public function getDetalleSolicitudes(array $params): array
    {
        // Para la tabla traemos los registros según los filtros (aprobadas por defecto, o si se desea todo)
        $where = $this->buildWhere($params, null); // Sin forzar estado para ver rechazadas/pendientes también
        $whereClause = $where['whereStr'] ? $where['whereStr'] : 'WHERE 1=1';

        $sql = "
            SELECT 
                s.id_solicitud_consumo,
                s.fecha_solicitud_consumo,
                CONCAT(TRIM(q.descripcion_quimico), ' - ', TRIM(q.fabricante_quimico)) AS nombre_quimico,
                ca.nombre_celula,
                TRIM(CONCAT(COALESCE(s.nombres_solicitante_consumo, ''), ' ', COALESCE(s.apellidos_solicitante_consumo, ''))) AS solicitante,
                COALESCE(u.descripcion_umb, 'Unidad') AS umb,
                COALESCE(q.precio_quimico, 0) AS precio_unitario,
                COALESCE(s.cantidad_solicitud_consumo, 0) AS cantidad_solicitada,
                COALESCE(s.cantidad_solicitud_consumo * q.precio_quimico, 0) AS costo_total,
                COALESCE(e.descripcion_estado, 'Desconocido') AS estado,
                s.id_estado_solicitud_quimico
            FROM quimicos_hwi_solicitudes_consumo s
            JOIN quimicos_hwi_quimicos q 
                ON s.id_quimico_solicitud_consumo = q.id_quimico
            LEFT JOIN quimicos_hwi_umbs u 
                ON q.id_umb_quimico = u.id_umb
            JOIN gestion_ambiental_hwi_celulas_areas ca 
                ON s.id_celula_area_solicitud_consumo = ca.id_celulas_areas
            JOIN gestion_ambiental_hwi_estados e 
                ON s.id_estado_solicitud_quimico = e.id_estado
            $whereClause
            ORDER BY s.fecha_solicitud_consumo DESC, s.id_solicitud_consumo DESC
            LIMIT 300
        ";
        return $this->execQuery($sql, $where['binds']);
    }
}
