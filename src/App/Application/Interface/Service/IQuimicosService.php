<?php

namespace App\Application\Interface\Service;

use App\Domain\DTO\QuimicosDTO;

interface IQuimicosService {
    public function onGetQuimicos(): array;
    public function deleteQuimico($id_quimico): bool;
    public function onGetCelulas(): array;
    public function onGetUmbs(): array;
    public function onGetQuimicosCelulasAreas_By__Id_Quimico(string $id_quimico): array;
    public function onGetQuimicos_By__Id(string $id_quimico): QuimicosDTO;
    public function onGetQuimicos_By__Id_Celula(int $id_celula): array;
    public function saveQuimicos(QuimicosDTO $quimicosDTO): bool;
    public function updateQuimicos(QuimicosDTO $quimicosDTO): bool;
}

?>