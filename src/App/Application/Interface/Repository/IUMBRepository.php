<?php

namespace App\Application\Interface\Repository;

use App\Domain\Model\UMB;

interface IUMBRepository {
    public function onGet(): array;
}

?>