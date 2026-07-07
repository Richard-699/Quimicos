<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ISolicitudesConsumoRepository;
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

    public function onGet_By__Id_Estado($id_estado): array
    {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_solicitudes_consumo WHERE id_estado_solicitud_quimico = ?");
        $stmt->execute([$id_estado]);
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
