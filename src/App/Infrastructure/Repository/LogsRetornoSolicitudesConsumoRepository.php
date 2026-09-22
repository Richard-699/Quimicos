<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ILogsRetornoSolicitudesConsumoRepository;
use App\Domain\Model\LogsRetornoSolicitudesConsumo;
use PDO;

class LogsRetornoSolicitudesConsumoRepository implements ILogsRetornoSolicitudesConsumoRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(LogsRetornoSolicitudesConsumo $logsRetornoSolicitudesConsumo): bool
    {
        $data = $logsRetornoSolicitudesConsumo->toArray();
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_logs_retorno_solicitudes_consumo ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }
}
