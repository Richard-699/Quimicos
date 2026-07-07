<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\CadenciaActual;

interface ICadenciaActualRepository {
    public function findByUltimaFecha(): ?CadenciaActual;
    public function save(CadenciaActual $cadenciaActual) : bool;
}

?>