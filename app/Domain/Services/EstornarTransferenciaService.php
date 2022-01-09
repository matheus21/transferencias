<?php

namespace App\Domain\Services;

use App\Domain\Models\Transferencia;
use App\Domain\Services\Contracts\CadastrarTransferencia;
use App\Domain\Services\Contracts\EstornarTransferencia;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class EstornarTransferenciaService implements EstornarTransferencia
{
    private TransferenciaRepositoryInterface $repository;
    private CadastrarTransferencia $cadastrarService;

    /**
     * @param TransferenciaRepositoryInterface $repository
     * @param CadastrarTransferencia $cadastrarService
     */
    public function __construct(
        TransferenciaRepositoryInterface $repository,
        CadastrarTransferencia $cadastrarService
    ) {
        $this->repository       = $repository;
        $this->cadastrarService = $cadastrarService;
    }


    public function estornar(int $idTransferencia): array
    {
        $transferencia = $this->repository->obter($idTransferencia);

        if (!$transferencia) {
            throw new NotFoundHttpException(trans('exception.transference.not_found'));
        }

        $this->validaSeTransferenciaPodeSerEstornada($transferencia);

        $dados = $this->montaDadosParaInserir($transferencia);
        $dados = $this->cadastrarService->cadastrar($dados);

        $this->repository->atualizar(
            $transferencia->id,
            ['status' => config('constants.status_transferencias.estornada')]
        );

        return $dados;
    }

    private function validaSeTransferenciaPodeSerEstornada(Transferencia $transferencia)
    {
        if ((int)$transferencia->status !== config('constants.status_transferencias.efetivada')) {
            throw new UnprocessableEntityHttpException(trans('exception.transference.reversal_not_allowed'));
        }
    }

    private function montaDadosParaInserir(Transferencia $transferencia): array
    {
        return [
            'valor'        => $transferencia->valor,
            'pagador'      => $transferencia->carteira_beneficiario_id,
            'beneficiario' => $transferencia->carteira_pagador_id
        ];
    }
}