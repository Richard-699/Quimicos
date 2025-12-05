<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\Peligrosidad;

interface IPeligrosidadesRepository {
    public function onGet(): array;
}

?>