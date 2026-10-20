<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\ConsumoAgua;

interface IConsumoAguaRepository
{
    public function getLastConsumosByCelula(int $id_celula): array;
    public function existeRegistroFecha(int $id_celula, ?int $id_tanque, string $fecha): bool;
    public function save(ConsumoAgua $consumoAgua): bool;
    public function findById(int $id): ?ConsumoAgua;
    public function update(ConsumoAgua $consumoAgua): bool;
}
