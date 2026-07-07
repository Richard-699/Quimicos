<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ICadenciaActualRepository;
use App\Domain\Model\CadenciaActual;
use PDO;

class CadenciaActualRepository implements ICadenciaActualRepository
{
    private $db;
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findByUltimaFecha(): ?CadenciaActual {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_cadencia_actual 
                                        WHERE fecha_hora_registro_cadencia_actual = (
                                        SELECT MAX(fecha_hora_registro_cadencia_actual) 
                                        FROM quimicos_hwi_cadencia_actual)
                                    ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return CadenciaActual::fromArray($row);
    }

    public function save(CadenciaActual $cadenciaActual) : bool{
        $data = $cadenciaActual->toArray();
        unset($data['id_cadencia_actual']);
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_cadencia_actual ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }
}
