<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\LogsIngresoInventario;

interface ILogsIngresoInventarioRepository {
    public function save(LogsIngresoInventario $logsIngresoInventario): bool;
}

?>