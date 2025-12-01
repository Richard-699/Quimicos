<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\SolicitudesConsumo;

interface ISolicitudesConsumoRepository {
    public function save(SolicitudesConsumo $solicitudesConsumo): bool;
}

?>