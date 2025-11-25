<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\QuimicosCelulasAreas;

interface IQuimicosCelulasAreasRepository {
    public function save(QuimicosCelulasAreas $quimicosCelulasAreas): bool;
}

?>