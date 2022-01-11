<?php

namespace App\Domain\Services;

use App\Domain\Services\Contracts\NotificarTransferencias;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class NotificarTransferenciasService implements NotificarTransferencias
{
    const NOTIFICACAO_ENVIADA = "Success";

    private TransferenciaRepositoryInterface $repository;
    private ClientInterface $guzzleClient;

    /**
     * @param TransferenciaRepositoryInterface $repository
     * @param ClientInterface $guzzleClient
     */
    public function __construct(TransferenciaRepositoryInterface $repository, ClientInterface $guzzleClient)
    {
        $this->repository   = $repository;
        $this->guzzleClient = $guzzleClient;
    }

    public function notificar(): void
    {
        $transferencias = $this->repository->obterTransferenciasPorFiltros(
            [
                'status'              => config('constants.status_transferencias.efetivada'),
                'notificacao_enviada' => false
            ]
        );

        foreach ($transferencias as $transferencia) {
            $this->notificaTransferencia($transferencia->id);
        }
    }

    private function notificaTransferencia(int $idTransferencia): void
    {
        try {
            $retorno = $this->guzzleClient->request('get', config('services.transference.notify'));
            $retorno = json_decode($retorno->getBody()->getContents());

            if (isset($retorno->message) && $retorno->message === self::NOTIFICACAO_ENVIADA) {
                $this->repository->atualizar($idTransferencia, ['notificacao_enviada' => true]);
            }
        } catch (ClientException $e) {
            return;
        }
    }
}