<?php

namespace App\Repositories\Contracts;

use App\Domain\Models\Carteira;

interface CarteiraRepositoryInteface extends Repository
{
    public function obter(int $idCarteira): Carteira;
}