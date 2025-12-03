<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\SolicitudesConsumo;

interface ISolicitudesConsumoRepository {
    public function save(SolicitudesConsumo $solicitudesConsumo): bool;
    public function onGet_By__Id_Estado(int $id_estado): array;
    public function update_Id_Estado_By__Id(int $id_solicitud, int $id_estado): bool;
}

?>