<?php

namespace App\Application\Interface\Service;

use App\Domain\DTO\ConsumoAguaDTO;

interface IConsumoAguaService
{
    public function onGetUltimosConsumos(int $id_celula): array;
    public function saveConsumoAgua(ConsumoAguaDTO $dto): bool;
    public function updateConsumoAgua(ConsumoAguaDTO $dto): bool;
}
