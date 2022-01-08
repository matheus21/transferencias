<?php

namespace App\Repositories;

use App\Domain\Models\Carteira;
use App\Repositories\Contracts\CarteiraRepositoryInteface;

class CarteiraRepository implements CarteiraRepositoryInteface
{
    public function model(): string
    {
        return Carteira::class;
    }

    public function obter(int $idCarteira): Carteira
    {
        return $this->model()::find($idCarteira);
    }
}