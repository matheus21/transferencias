<?php

namespace App\Repositories\Contracts;

use App\Domain\Models\Transferencia;
use Illuminate\Database\Eloquent\Collection;

interface TransferenciaRepositoryInterface extends Repository
{
    public function inserir(array $dados): Transferencia;

    public function obterTransferenciasNaoEfetivadasPorPagador(int $idCarteiraPagador): Collection;

    public function obterTransferenciasPorFiltros(array $filtros): Collection;

    public function atualizar(int $idTransferencia, array $dados): bool;

    public function obter(int $idTransferencia): ?Transferencia;
}