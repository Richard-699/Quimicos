<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\LogsRetornoSolicitudesConsumo;

interface ILogsRetornoSolicitudesConsumoRepository {
    public function save(LogsRetornoSolicitudesConsumo $logsRetornoSolicitudesConsumo): bool;
}

?>