<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\LogsPrecios;

interface ILogsPreciosRepository {
    public function save(LogsPrecios $logsPrecios): bool;
}

?>