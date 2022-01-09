<?php

namespace App\Repositories;

use App\Domain\Models\Transferencia;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TransferenciaRepository implements TransferenciaRepositoryInterface
{
    public function inserir(array $dados): Transferencia
    {
        return $this->model()::create($dados);
    }

    public function model(): string
    {
        return Transferencia::class;
    }

    public function obterTransferenciasPorStatus(int $status): Collection
    {
        return $this->model()::where(
            'status',
            $status
        )->get();
    }

    public function atualizar(int $idTransferencia, array $dados): bool
    {
        return $this->model()::find($idTransferencia)->update($dados);
    }

    public function obterTransferenciasNaoEfetivadasPorPagador(int $idCarteiraPagador): Collection
    {
        return $this->model()::where(
            [
                ['carteira_pagador_id', $idCarteiraPagador],
                ['status', config('constants.status_transferencias.nao_efetivada')]
            ]
        )->get();
    }

    public function obter(int $idTransferencia): ?Transferencia
    {
        return $this->model()::find($idTransferencia);
    }
}