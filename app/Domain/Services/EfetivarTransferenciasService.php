<?php

namespace App\Domain\Services;

use App\Domain\Models\Transferencia;
use App\Domain\Services\Contracts\EfetivarTransferencias;
use App\Repositories\Contracts\CarteiraRepositoryInteface;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;

class EfetivarTransferenciasService implements EfetivarTransferencias
{
    const TRANSFERENCIA_AUTORIZADA = "Autorizado";

    private TransferenciaRepositoryInterface $repository;
    private CarteiraRepositoryInteface $carteiraRepository;
    private ClientInterface $guzzleClient;

    /**
     * @param TransferenciaRepositoryInterface $repository
     * @param CarteiraRepositoryInteface $carteiraRepository
     * @param ClientInterface $guzzleClient
     */
    public function __construct(
        TransferenciaRepositoryInterface $repository,
        CarteiraRepositoryInteface $carteiraRepository,
        ClientInterface $guzzleClient
    ) {
        $this->repository         = $repository;
        $this->carteiraRepository = $carteiraRepository;
        $this->guzzleClient       = $guzzleClient;
    }

    public function efetivar(): void
    {
        $transferencias = $this->repository->obterTransferenciasPorStatus(config('constants.status_transferencias.nao_efetivada'));

        foreach ($transferencias as $transferencia) {
            $this->validaAutorizacao($transferencia);
        }
    }

    private function validaAutorizacao(Transferencia $transferencia): void
    {
        try {
            $retorno = $this->guzzleClient->request('get', config('services.transference.authorize'));
            $retorno = json_decode($retorno->getBody()->getContents());

            if ($retorno->message && $retorno->message === self::TRANSFERENCIA_AUTORIZADA) {
                $this->autorizaTransferencia($transferencia);

                return;
            }

            $this->repository->atualizar(
                $transferencia->id,
                ['status' => config('constants.status_transferencias.recusada')]
            );
        } catch (ClientException $e) {
            return;
        }
    }

    private function autorizaTransferencia(Transferencia $transferencia): void
    {
        $this->atualizaCarteirasDoPagadorEBeneficiario($transferencia);

        $this->repository->atualizar(
            $transferencia->id,
            ['status' => config('constants.status_transferencias.efetivada')]
        );
    }

    private function atualizaCarteirasDoPagadorEBeneficiario(Transferencia $transferencia): void
    {
        $carteiraPagador      = $this->carteiraRepository->obter($transferencia->carteira_pagador_id);
        $carteiraBeneficiario = $this->carteiraRepository->obter($transferencia->carteira_beneficiario_id);

        $saldoPagador      = $carteiraPagador->saldo - $transferencia->valor;
        $saldoBeneficiario = $carteiraBeneficiario->saldo + $transferencia->valor;

        $this->carteiraRepository->atualizar($carteiraPagador->id, ['saldo' => $saldoPagador]);
        $this->carteiraRepository->atualizar($carteiraBeneficiario->id, ['saldo' => $saldoBeneficiario]);
    }
}