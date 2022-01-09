<?php

namespace App\Domain\Services\Contracts;

interface EstornarTransferencia
{
    public function estornar(int $idTransferencia): array;
}