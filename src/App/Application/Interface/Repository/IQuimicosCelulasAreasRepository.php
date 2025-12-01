<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\QuimicosCelulasAreas;

interface IQuimicosCelulasAreasRepository {
    public function save(QuimicosCelulasAreas $quimicosCelulasAreas): bool;
    public function onGet_By__Id_Quimico(string $id_quimico): array;
    public function delete_By__Id_Quimico(string $id_quimico): bool;
}

?>