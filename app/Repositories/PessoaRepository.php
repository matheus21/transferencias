<?php

namespace App\Repositories;

use App\Domain\Models\Pessoa;
use App\Repositories\Contracts\PessoaRepositoryInterface;

class PessoaRepository implements PessoaRepositoryInterface
{

    public function obter(int $idPessoa): Pessoa
    {
        return $this->model()::find($idPessoa);
    }

    public function model(): string
    {
        return Pessoa::class;
    }
}