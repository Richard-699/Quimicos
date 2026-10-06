<?php

namespace App\Infrastructure\Repository;

use PDO;
use Exception;

class IndicadorConsumoAguaRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function execQuery(string $sql, array $binds = []): array
    {
        $stmt = $this->db->prepare($sql);
        foreach ($binds as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCelulas(): array
    {
        return $this->execQuery(
            "SELECT DISTINCT ca.id_celulas_areas, ca.nombre_celula
             FROM gestion_ambiental_hwi_celulas_areas ca
             INNER JOIN gestion_ambiental_hwi_consumo_agua c ON c.id_celula_consumo_agua = ca.id_celulas_areas
             ORDER BY ca.nombre_celula ASC"
        );
    }

    public function getTanques(): array
    {
        return $this->execQuery(
            "SELECT id_tanque_abastecimiento_agua, tanque_abastecimiento_agua
             FROM gestion_ambiental_hwi_tanques_abastecimiento_agua
             ORDER BY tanque_abastecimiento_agua ASC"
        );
    }

    public function getAnios(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT DISTINCT YEAR(fecha_consumo_agua) AS anio
                 FROM gestion_ambiental_hwi_consumo_agua
                 ORDER BY anio DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return [(int)date('Y')];
        }
    }

    public function getConsumoPrincipalMensual(): array
    {
        $sql = "
            SELECT 
                DATE_FORMAT(fecha_consumo_agua, '%Y-%m-01') AS fecha_mes,
                YEAR(fecha_consumo_agua) AS anio,
                MONTH(fecha_consumo_agua) AS mes,
                SUM(consumo_final_agua - consumo_inicial_agua) AS consumo_ppal
            FROM gestion_ambiental_hwi_consumo_agua
            WHERE id_tanque_abastecimiento_consumo_agua = 6
            GROUP BY fecha_mes, anio, mes
            ORDER BY fecha_mes ASC
        ";
        return $this->execQuery($sql);
    }

    public function getConsumoCelulasMensualSinLAP(): array
    {
        $sql = "
            SELECT 
                DATE_FORMAT(fecha_consumo_agua, '%Y-%m-01') AS fecha_mes,
                YEAR(fecha_consumo_agua) AS anio,
                MONTH(fecha_consumo_agua) AS mes,
                SUM(consumo_final_agua - consumo_inicial_agua) AS consumo_cells
            FROM gestion_ambiental_hwi_consumo_agua
            WHERE id_celula_consumo_agua != 21 
              AND (id_tanque_abastecimiento_consumo_agua != 6 OR id_tanque_abastecimiento_consumo_agua IS NULL)
            GROUP BY fecha_mes, anio, mes
            ORDER BY fecha_mes ASC
        ";
        return $this->execQuery($sql);
    }

    public function getKpi(string $whereStr, array $binds): array
    {
        try {
            $sql = "
                SELECT
                    SUM(c.consumo_final_agua - c.consumo_inicial_agua)   AS total_consumo,
                    AVG(c.consumo_final_agua - c.consumo_inicial_agua)   AS promedio_diario,
                    MAX(c.consumo_final_agua - c.consumo_inicial_agua)   AS pico_maximo,
                    COUNT(*)                                               AS total_registros,
                    SUM(CASE WHEN ml.supera_tope = 1 THEN 1 ELSE 0 END)  AS total_alertas,
                    AVG(ml.tope_calculado)                                AS tope_promedio
                FROM gestion_ambiental_hwi_consumo_agua c
                LEFT JOIN gestion_ambiental_hwi_consumo_agua_ml_scores ml
                    ON c.id_consumo_agua = ml.id_consumo_agua_score
                $whereStr
            ";
            $res = $this->execQuery($sql, $binds);
            return $res[0] ?? [];
        } catch (Exception $e) {
            $sql = "
                SELECT
                    SUM(consumo_final_agua - consumo_inicial_agua) AS total_consumo,
                    AVG(consumo_final_agua - consumo_inicial_agua) AS promedio_diario,
                    MAX(consumo_final_agua - consumo_inicial_agua) AS pico_maximo,
                    COUNT(*)                                        AS total_registros,
                    0                                               AS total_alertas,
                    NULL                                            AS tope_promedio
                FROM gestion_ambiental_hwi_consumo_agua c
                $whereStr
            ";
            $res = $this->execQuery($sql, $binds);
            return $res[0] ?? [];
        }
    }

    public function getRegistros(string $whereStr, array $binds): array
    {
        try {
            $sql = "
                SELECT
                    c.id_consumo_agua,
                    c.fecha_consumo_agua,
                    c.id_celula_consumo_agua,
                    ca.nombre_celula,
                    c.id_tanque_abastecimiento_consumo_agua,
                    t.tanque_abastecimiento_agua AS nombre_tanque,
                    c.consumo_inicial_agua,
                    c.consumo_final_agua,
                    (c.consumo_final_agua - c.consumo_inicial_agua) AS consumo_neto,
                    ml.supera_tope,
                    ml.tope_calculado,
                    ml.score_anomalia
                FROM gestion_ambiental_hwi_consumo_agua c
                LEFT JOIN gestion_ambiental_hwi_celulas_areas ca
                    ON c.id_celula_consumo_agua = ca.id_celulas_areas
                LEFT JOIN gestion_ambiental_hwi_tanques_abastecimiento_agua t
                    ON c.id_tanque_abastecimiento_consumo_agua = t.id_tanque_abastecimiento_agua
                LEFT JOIN gestion_ambiental_hwi_consumo_agua_ml_scores ml
                    ON c.id_consumo_agua = ml.id_consumo_agua_score
                $whereStr
                ORDER BY c.fecha_consumo_agua DESC
                LIMIT 1000
            ";
            return $this->execQuery($sql, $binds);
        } catch (Exception $e) {
            $sql = "
                SELECT
                    c.id_consumo_agua, c.fecha_consumo_agua,
                    c.id_celula_consumo_agua, ca.nombre_celula,
                    c.id_tanque_abastecimiento_consumo_agua,
                    t.tanque_abastecimiento_agua AS nombre_tanque,
                    c.consumo_inicial_agua, c.consumo_final_agua,
                    (c.consumo_final_agua - c.consumo_inicial_agua) AS consumo_neto,
                    NULL AS supera_tope, NULL AS tope_calculado, NULL AS score_anomalia
                FROM gestion_ambiental_hwi_consumo_agua c
                LEFT JOIN gestion_ambiental_hwi_celulas_areas ca
                    ON c.id_celula_consumo_agua = ca.id_celulas_areas
                LEFT JOIN gestion_ambiental_hwi_tanques_abastecimiento_agua t
                    ON c.id_tanque_abastecimiento_consumo_agua = t.id_tanque_abastecimiento_agua
                $whereStr
                ORDER BY c.fecha_consumo_agua DESC
                LIMIT 1000
            ";
            return $this->execQuery($sql, $binds);
        }
    }

    public function getConsumoPorMes(string $whereStrMes, array $bindsMes): array
    {
        $sql = "
            SELECT
                YEAR(c.fecha_consumo_agua)  AS anio,
                MONTH(c.fecha_consumo_agua) AS mes,
                SUM(c.consumo_final_agua - c.consumo_inicial_agua) AS consumo_total
            FROM gestion_ambiental_hwi_consumo_agua c
            $whereStrMes
            GROUP BY anio, mes
            ORDER BY anio, mes
        ";
        return $this->execQuery($sql, $bindsMes);
    }

    public function getConsumoPorCelula(string $whereStr, array $binds): array
    {
        $sql = "
            SELECT
                COALESCE(ca.nombre_celula, 'Sin célula') AS nombre_celula,
                SUM(c.consumo_final_agua - c.consumo_inicial_agua) AS consumo_total
            FROM gestion_ambiental_hwi_consumo_agua c
            LEFT JOIN gestion_ambiental_hwi_celulas_areas ca
                ON c.id_celula_consumo_agua = ca.id_celulas_areas
            $whereStr
            GROUP BY ca.nombre_celula
            ORDER BY consumo_total DESC
        ";
        return $this->execQuery($sql, $binds);
    }

    public function getConsumoPorTanque(string $whereStr, array $binds): array
    {
        $sql = "
            SELECT
                COALESCE(t.tanque_abastecimiento_agua, 'Sin tanque') AS nombre_tanque,
                SUM(c.consumo_final_agua - c.consumo_inicial_agua)   AS consumo_total
            FROM gestion_ambiental_hwi_consumo_agua c
            LEFT JOIN gestion_ambiental_hwi_tanques_abastecimiento_agua t
                ON c.id_tanque_abastecimiento_consumo_agua = t.id_tanque_abastecimiento_agua
            $whereStr
            GROUP BY t.tanque_abastecimiento_agua
            ORDER BY consumo_total DESC
        ";
        return $this->execQuery($sql, $binds);
    }

    public function getSerieTemporal(string $whereStr, array $binds): array
    {
        try {
            $sql = "
                SELECT
                    DATE(c.fecha_consumo_agua)                             AS fecha,
                    SUM(c.consumo_final_agua - c.consumo_inicial_agua)     AS consumo_total,
                    AVG(ml.tope_calculado)                                  AS tope_ml
                FROM gestion_ambiental_hwi_consumo_agua c
                LEFT JOIN gestion_ambiental_hwi_consumo_agua_ml_scores ml
                    ON c.id_consumo_agua = ml.id_consumo_agua_score
                $whereStr
                GROUP BY DATE(c.fecha_consumo_agua)
                ORDER BY fecha ASC
            ";
            return $this->execQuery($sql, $binds);
        } catch (Exception $e) {
            $sql = "
                SELECT
                    DATE(c.fecha_consumo_agua)                         AS fecha,
                    SUM(c.consumo_final_agua - c.consumo_inicial_agua) AS consumo_total,
                    NULL                                               AS tope_ml
                FROM gestion_ambiental_hwi_consumo_agua c
                $whereStr
                GROUP BY DATE(c.fecha_consumo_agua)
                ORDER BY fecha ASC
            ";
            return $this->execQuery($sql, $binds);
        }
    }

    public function getAlertasML(string $alertaWhere, array $alertaBinds): array
    {
        try {
            $sql = "
                SELECT
                    ml.id_score,
                    c.fecha_consumo_agua,
                    ca.nombre_celula,
                    COALESCE(t.tanque_abastecimiento_agua, 'N/A') AS nombre_tanque,
                    (c.consumo_final_agua - c.consumo_inicial_agua) AS consumo_neto,
                    ml.tope_calculado,
                    ml.score_anomalia,
                    ml.supera_tope
                FROM gestion_ambiental_hwi_consumo_agua_ml_scores ml
                JOIN gestion_ambiental_hwi_consumo_agua c
                    ON ml.id_consumo_agua_score = c.id_consumo_agua
                LEFT JOIN gestion_ambiental_hwi_celulas_areas ca
                    ON c.id_celula_consumo_agua = ca.id_celulas_areas
                LEFT JOIN gestion_ambiental_hwi_tanques_abastecimiento_agua t
                    ON c.id_tanque_abastecimiento_consumo_agua = t.id_tanque_abastecimiento_agua
                WHERE ml.supera_tope = 1
                $alertaWhere
                ORDER BY c.fecha_consumo_agua DESC
                LIMIT 50
            ";
            return $this->execQuery($sql, $alertaBinds);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getHistoricoMensual(string $whereStr, array $binds): array
    {
        try {
            $sql = "
                SELECT
                    DATE_FORMAT(c.fecha_consumo_agua, '%Y-%m-01') AS fecha_mes,
                    SUM(c.consumo_final_agua - c.consumo_inicial_agua)  AS consumo_mensual,
                    AVG(ml.tope_calculado)                               AS tope_mensual
                FROM gestion_ambiental_hwi_consumo_agua c
                LEFT JOIN gestion_ambiental_hwi_consumo_agua_ml_scores ml
                    ON c.id_consumo_agua = ml.id_consumo_agua_score
                $whereStr
                GROUP BY fecha_mes
                ORDER BY fecha_mes ASC
                LIMIT 36
            ";
            return $this->execQuery($sql, $binds);
        } catch (Exception $e) {
            $sql = "
                SELECT
                    DATE_FORMAT(fecha_consumo_agua, '%Y-%m-01') AS fecha_mes,
                    SUM(consumo_final_agua - consumo_inicial_agua) AS consumo_mensual,
                    NULL AS tope_mensual
                FROM gestion_ambiental_hwi_consumo_agua c
                $whereStr
                GROUP BY fecha_mes
                ORDER BY fecha_mes ASC
                LIMIT 36
            ";
            return $this->execQuery($sql, $binds);
        }
    }
}
