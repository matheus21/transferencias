<?php

namespace App\Domain\Services;

use App\Domain\Models\Carteira;
use App\Repositories\Contracts\CarteiraRepositoryInteface;
use App\Repositories\Contracts\PessoaRepositoryInterface;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Domain\Services\Contracts\CadastrarTransferencia;

class CadastrarTransferenciaService implements CadastrarTransferencia
{
    private TransferenciaRepositoryInterface $repository;
    private CarteiraRepositoryInteface $carteiraRepository;
    private PessoaRepositoryInterface $pessoaRepository;

    /**
     * @param TransferenciaRepositoryInterface $repository
     * @param CarteiraRepositoryInteface $carteiraRepository
     * @param PessoaRepositoryInterface $pessoaRepository
     */
    public function __construct(
        TransferenciaRepositoryInterface $repository,
        CarteiraRepositoryInteface $carteiraRepository,
        PessoaRepositoryInterface $pessoaRepository
    ) {
        $this->repository         = $repository;
        $this->carteiraRepository = $carteiraRepository;
        $this->pessoaRepository   = $pessoaRepository;
    }

    public function cadastrar(array $dados): array
    {
        $this->validaSeTransferenciaPodeSerRealizada($dados['pagador'], $dados['valor']);
        $dados         = $this->montaDadosParaInserir($dados);
        $transferencia = $this->repository->inserir($dados);

        return $transferencia->toArray();
    }

    private function validaSeTransferenciaPodeSerRealizada(
        int $idCarteiraPagador,
        float $valorATransferir
    ): void {
        $carteiraPagador = $this->carteiraRepository->obter($idCarteiraPagador);
        $this->validaSeCarteiraPertenceAPessoaFisica($carteiraPagador->pessoa_id);
        $this->validaSeExisteSaldoSuficienteParaTransferir($carteiraPagador, $valorATransferir);
    }

    private function validaSeCarteiraPertenceAPessoaFisica(int $idPessoa): void
    {
        $pessoa = $this->pessoaRepository->obter($idPessoa);

        if ($pessoa->tipo_pessoa == config('constants.tipos_pessoas.pessoa_juridica')) {
            throw new AccessDeniedHttpException(trans('exception.transference.not_allowed'));
        }
    }

    private function validaSeExisteSaldoSuficienteParaTransferir(
        Carteira $carteiraPagador,
        float $valorATransferir
    ): void {
        $transferencias      = $this->repository->obterTransferenciasNaoEfetivadasPorPagador($carteiraPagador->id);
        $saldoCarteira       = $carteiraPagador->saldo;
        $valorTransferencias = $transferencias->pluck('valor')->sum();

        if (($saldoCarteira - $valorTransferencias) < $valorATransferir) {
            throw new UnprocessableEntityHttpException(trans('exception.transference.insufficient_funds'));
        }
    }

    private function montaDadosParaInserir(array $dados): array
    {
        return [
            'carteira_pagador_id'      => $dados['pagador'],
            'carteira_beneficiario_id' => $dados['beneficiario'],
            'valor'                    => $dados['valor'],
            'status'                   => config('constants.status_transferencias.nao_efetivada'),
        ];
    }
}