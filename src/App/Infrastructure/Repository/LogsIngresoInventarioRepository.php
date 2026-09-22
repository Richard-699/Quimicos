<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ILogsIngresoInventarioRepository;
use App\Domain\Model\LogsIngresoInventario;
use PDO;

class LogsIngresoInventarioRepository implements ILogsIngresoInventarioRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(LogsIngresoInventario $logsIngresoInventario): bool
    {
        $data = $logsIngresoInventario->toArray();
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_logs_ingreso_inventario ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }
}
