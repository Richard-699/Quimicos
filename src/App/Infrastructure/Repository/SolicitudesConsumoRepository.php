<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ISolicitudesConsumoRepository;
use App\Domain\Enum\EstadoSolicitud;
use App\Domain\Model\SolicitudesConsumo;
use Override;
use PDO;

class SolicitudesConsumoRepository implements ISolicitudesConsumoRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(SolicitudesConsumo $solicitudesConsumo): bool
    {
        $data = $solicitudesConsumo->toArray();
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_solicitudes_consumo ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }

    public function findBy_IdEstadoAndFechaMinima(string $fecha_minima): array
    {
        $sql = "SELECT * FROM quimicos_hwi_solicitudes_consumo 
                WHERE (
                    id_estado_solicitud_quimico = :id_pendiente 
                    OR (id_estado_solicitud_quimico = :id_aprobado AND fecha_solicitud_consumo >= :fecha_minima)
                )
                AND cantidad_consumo_actualizada IS NULL
                ORDER BY 
                    CASE 
                        WHEN id_estado_solicitud_quimico = :id_pendiente THEN 1 
                        ELSE 2 
                    END ASC,
                    fecha_solicitud_consumo DESC;";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_pendiente' => EstadoSolicitud::PENDIENTE->value,
            'id_aprobado'  => EstadoSolicitud::APROBADO->value,
            'fecha_minima' => $fecha_minima
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([SolicitudesConsumo::class, 'fromArray'], $rows);
    }

    public function update_Id_Estado_By__Id(int $id_solicitud, int $id_estado): bool
    {
        $query = "UPDATE quimicos_hwi_solicitudes_consumo 
                    SET id_estado_solicitud_quimico = :id_estado
                    WHERE id_solicitud_consumo = :id_solicitud";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_estado', $id_estado);
        $stmt->bindParam(':id_solicitud', $id_solicitud);

        return $stmt->execute();
    }

    public function update_Consumo_By__Id(SolicitudesConsumo $solicitudesConsumo): bool
    {
        $query = "UPDATE quimicos_hwi_solicitudes_consumo 
                    SET cantidad_consumo_actualizada = :cantidad_consumo_actualizada,
                    cantidad_solicitud_consumo =:cantidad_solicitud_consumo
                    WHERE id_solicitud_consumo = :id_solicitud";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cantidad_consumo_actualizada', $solicitudesConsumo->cantidad_consumo_actualizada);
        $stmt->bindParam(':cantidad_solicitud_consumo', $solicitudesConsumo->cantidad_solicitud_consumo);
        $stmt->bindParam(':id_solicitud', $solicitudesConsumo->id_solicitud_consumo);

        return $stmt->execute();
    }

    public function findUltimaFechaSolicitudConsumo_By__IdQuimico_And_IdCelula(string $id_quimico, int $id_celula_area): ?string
    {
        $id_estado_aprobada = 1;

        $query = "SELECT fecha_solicitud_consumo
              FROM quimicos_hwi_solicitudes_consumo
              WHERE id_quimico_solicitud_consumo = :id_quimico
                AND id_celula_area_solicitud_consumo = :id_celula_area
                AND id_estado_solicitud_quimico = :id_estado
              ORDER BY fecha_solicitud_consumo DESC
              LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_quimico', $id_quimico);
        $stmt->bindParam(':id_celula_area', $id_celula_area);
        $stmt->bindParam(':id_estado', $id_estado_aprobada, PDO::PARAM_INT);

        $stmt->execute();

        $fecha = $stmt->fetchColumn();

        return $fecha ?: null;
    }
}
