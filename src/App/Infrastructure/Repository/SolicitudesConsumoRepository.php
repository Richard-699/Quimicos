<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ISolicitudesConsumoRepository;
use App\Domain\Model\SolicitudesConsumo;
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
}
