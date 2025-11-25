<?php

namespace App\Application\Interface\Repository;

use App\Domain\DTO\QuimicosDTO;
use App\Domain\Model\Quimicos;

interface IQuimicosRepository {
    public function onGet_By__Id_Estado(int $id_estado): array;
    public function delete_By__Id_Quimico(string $id_quimico, int $id_estado): bool;
}

?>