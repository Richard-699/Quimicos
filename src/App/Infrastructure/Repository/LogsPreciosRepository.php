<?php

namespace App\Infrastructure\Repository;

use App\Domain\Model\LogsPrecios;
use App\Application\Interface\Repository\ILogsPreciosRepository;
use PDO;

class LogsPreciosRepository implements ILogsPreciosRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(LogsPrecios $logsPrecios): bool
    {
        $data = $logsPrecios->toArray();
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_logs_precios ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }
}
