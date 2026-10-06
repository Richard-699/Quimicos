<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\IConsumoAguaRepository;
use App\Domain\Model\ConsumoAgua;
use App\Domain\DTO\ConsumoAguaDTO;
use PDO;

class ConsumoAguaRepository implements IConsumoAguaRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getLastConsumosByCelula(int $id_celula): array
    {
        // Traemos los últimos registros de esa célula agrupados por tanque (o null).
        // Dado que puede haber un registro por tanque en un día determinado,
        // traemos los registros cuya fecha sea la máxima fecha registrada para ese tanque en esa célula.
        
        $query = "
            SELECT 
                c.id_consumo_agua,
                c.fecha_consumo_agua,
                c.id_celula_consumo_agua,
                c.id_tanque_abastecimiento_consumo_agua,
                c.consumo_inicial_agua,
                c.consumo_final_agua,
                t.tanque_abastecimiento_agua as nombre_tanque
            FROM gestion_ambiental_hwi_consumo_agua c
            LEFT JOIN gestion_ambiental_hwi_tanques_abastecimiento_agua t 
                ON c.id_tanque_abastecimiento_consumo_agua = t.id_tanque_abastecimiento_agua
            INNER JOIN (
                SELECT 
                    id_tanque_abastecimiento_consumo_agua, 
                    MAX(fecha_consumo_agua) as max_fecha
                FROM gestion_ambiental_hwi_consumo_agua
                WHERE id_celula_consumo_agua = :id_celula
                GROUP BY id_tanque_abastecimiento_consumo_agua
            ) max_c ON 
                (c.id_tanque_abastecimiento_consumo_agua = max_c.id_tanque_abastecimiento_consumo_agua OR (c.id_tanque_abastecimiento_consumo_agua IS NULL AND max_c.id_tanque_abastecimiento_consumo_agua IS NULL))
                AND c.fecha_consumo_agua = max_c.max_fecha
            WHERE c.id_celula_consumo_agua = :id_celula
            ORDER BY c.fecha_consumo_agua DESC, t.tanque_abastecimiento_agua ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_celula', $id_celula, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($rows as $row) {
            $result[] = new ConsumoAguaDTO(
                $row['id_consumo_agua'],
                $row['fecha_consumo_agua'],
                $row['id_celula_consumo_agua'],
                $row['id_tanque_abastecimiento_consumo_agua'],
                $row['consumo_inicial_agua'],
                $row['consumo_final_agua'],
                $row['nombre_tanque']
            );
        }
        
        return $result;
    }
    
    public function existeRegistroFecha(int $id_celula, ?int $id_tanque, string $fecha): bool
    {
        // $fecha can be just 'YYYY-MM-DD', so we check using DATE()
        $query = "
            SELECT COUNT(*) as count 
            FROM gestion_ambiental_hwi_consumo_agua 
            WHERE id_celula_consumo_agua = :id_celula 
            AND DATE(fecha_consumo_agua) = :fecha
        ";
        
        if ($id_tanque !== null) {
            $query .= " AND id_tanque_abastecimiento_consumo_agua = :id_tanque";
        } else {
            $query .= " AND id_tanque_abastecimiento_consumo_agua IS NULL";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_celula', $id_celula, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha);
        if ($id_tanque !== null) {
            $stmt->bindParam(':id_tanque', $id_tanque, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['count'] > 0;
    }

    public function save(ConsumoAgua $consumoAgua): bool
    {
        $query = "
            INSERT INTO gestion_ambiental_hwi_consumo_agua 
            (fecha_consumo_agua, id_celula_consumo_agua, id_tanque_abastecimiento_consumo_agua, consumo_inicial_agua, consumo_final_agua)
            VALUES 
            (:fecha, :id_celula, :id_tanque, :inicial, :final)
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':fecha', $consumoAgua->fecha_consumo_agua);
        $stmt->bindParam(':id_celula', $consumoAgua->id_celula_consumo_agua, PDO::PARAM_INT);
        $stmt->bindParam(':id_tanque', $consumoAgua->id_tanque_abastecimiento_consumo_agua, $consumoAgua->id_tanque_abastecimiento_consumo_agua === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':inicial', $consumoAgua->consumo_inicial_agua);
        $stmt->bindParam(':final', $consumoAgua->consumo_final_agua);
        
        return $stmt->execute();
    }

    public function findById(int $id): ?ConsumoAgua
    {
        $query = "
            SELECT 
                id_consumo_agua,
                fecha_consumo_agua,
                id_celula_consumo_agua,
                id_tanque_abastecimiento_consumo_agua,
                consumo_inicial_agua,
                consumo_final_agua
            FROM gestion_ambiental_hwi_consumo_agua
            WHERE id_consumo_agua = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new ConsumoAgua(
            $row['id_consumo_agua'],
            $row['fecha_consumo_agua'],
            $row['id_celula_consumo_agua'],
            $row['id_tanque_abastecimiento_consumo_agua'],
            $row['consumo_inicial_agua'] !== null ? (float)$row['consumo_inicial_agua'] : null,
            $row['consumo_final_agua'] !== null ? (float)$row['consumo_final_agua'] : null
        );
    }

    public function update(ConsumoAgua $consumoAgua): bool
    {
        $query = "
            UPDATE gestion_ambiental_hwi_consumo_agua 
            SET consumo_final_agua = :final
            WHERE id_consumo_agua = :id
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':final', $consumoAgua->consumo_final_agua);
        $stmt->bindParam(':id', $consumoAgua->id_consumo_agua, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
