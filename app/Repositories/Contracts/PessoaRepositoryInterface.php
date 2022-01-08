<?php

namespace App\Repositories\Contracts;

use App\Domain\Models\Pessoa;

interface PessoaRepositoryInterface extends Repository
{
    public function obter(int $idPessoa): Pessoa;
}