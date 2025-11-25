<?php

namespace App\Application\Interface\Service;

use App\Domain\DTO\QuimicosDTO;

interface IQuimicosService {
    public function onGetQuimicos(): array;
    public function deleteQuimico($id_quimico): bool;
    public function onGetCelulas(): array;
    public function onGetUmbs(): array;
    public function saveQuimicos(QuimicosDTO $quimicosDTO): bool;
}

?>