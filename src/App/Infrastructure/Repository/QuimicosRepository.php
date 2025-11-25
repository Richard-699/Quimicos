<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\IQuimicosRepository;
use App\Domain\Model\Quimicos;
use PDO;

class QuimicosRepository implements IQuimicosRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function onGet_By__Id_Estado($id_estado): array {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_quimicos WHERE id_estado_quimico = ?");
        $stmt->execute([$id_estado]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map([Quimicos::class, 'fromArray'], $rows);
    }

    public function delete_By__Id_Quimico($id_quimico, $id_estado): bool {
        $query = "UPDATE quimicos_hwi_quimicos 
                    SET id_estado_quimico = :id_estado
                    WHERE id_quimico = :id_quimico";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_estado', $id_estado);
        $stmt->bindParam(':id_quimico', $id_quimico);

        return $stmt->execute();
    }

    public function save(Quimicos $quimicos): bool {
        $data = $quimicos->toArray();
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_quimicos ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }
}
