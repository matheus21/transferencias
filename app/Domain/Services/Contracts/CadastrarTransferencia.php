<?php

namespace App\Domain\Services\Contracts;

interface CadastrarTransferencia
{
    public function cadastrar(array $dados): array;
}