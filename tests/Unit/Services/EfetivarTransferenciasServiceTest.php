<?php

namespace Tests\Unit\Services;

use App\Domain\Models\Carteira;
use App\Domain\Models\Transferencia;
use App\Domain\Services\Contracts\EfetivarTransferencias;
use App\Repositories\Contracts\CarteiraRepositoryInteface;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

class EfetivarTransferenciasServiceTest extends TestCase
{
    const TRANSFERENCIA_AUTORIZADA = "Autorizado";
    const TRANSFERENCIA_RECUSADA   = "Recusado";

    private EfetivarTransferencias $service;
    private TransferenciaRepositoryInterface $repository;
    private CarteiraRepositoryInteface $carteiraRepository;
    private ClientInterface $guzzleClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository         = \Mockery::mock(TransferenciaRepositoryInterface::class);
        $this->carteiraRepository = \Mockery::mock(CarteiraRepositoryInteface::class);
        $this->guzzleClient       = \Mockery::mock(ClientInterface::class);

        $this->app->instance(TransferenciaRepositoryInterface::class, $this->repository);
        $this->app->instance(CarteiraRepositoryInteface::class, $this->carteiraRepository);
        $this->app->instance(ClientInterface::class, $this->guzzleClient);

        $this->service = $this->app->make(EfetivarTransferencias::class);
    }

    /**
     * @test
     */
    public function deveEfetivarTransferencias()
    {
        $headers  = ['Content-Type' => 'application/json'];
        $dados    = json_encode(['message' => self::TRANSFERENCIA_AUTORIZADA]);
        $resposta = new GuzzleResponse(Response::HTTP_OK, $headers, $dados);

        $idCarteiraPagador      = $this->faker->randomDigitNotZero();
        $idCarteiraBeneficiario = $this->faker->randomDigitNot($idCarteiraPagador);

        $carteiraPagador          = Carteira::factory()->make();
        $carteiraPagador->id      = $idCarteiraPagador;
        $carteiraBeneficiario     = Carteira::factory()->make();
        $carteiraBeneficiario->id = $idCarteiraBeneficiario;

        $valorTransferencia = $this->faker->randomFloat(2, 1, $carteiraPagador->saldo);

        $transferencias = Transferencia::factory(1)->make(
            [
                'carteira_pagador_id'      => $idCarteiraPagador,
                'carteira_beneficiario_id' => $idCarteiraBeneficiario,
                'valor'                    => $valorTransferencia
            ]
        );

        $transferencias->first()->id = $this->faker->randomDigitNotZero();

        $novoSaldoPagador      = $carteiraPagador->saldo - $valorTransferencia;
        $novoSaldoBeneficiario = $carteiraBeneficiario->saldo + $valorTransferencia;

        $this->repository->shouldReceive('obterTransferenciasPorStatus')->with(config('constants.status_transferencias.nao_efetivada'))->once()->andReturn($transferencias);
        $this->guzzleClient->shouldReceive('request')->withArgs(
            [
                'get',
                config('services.transference.authorize')
            ]
        )->andReturn($resposta);

        $this->carteiraRepository->shouldReceive('obter')->with($idCarteiraPagador)->once()->andReturn($carteiraPagador);
        $this->carteiraRepository->shouldReceive('obter')->with($idCarteiraBeneficiario)->once()->andReturn($carteiraBeneficiario);

        $this->carteiraRepository->shouldReceive('atualizar')->withArgs(
            [
                $carteiraPagador->id,
                ['saldo' => $novoSaldoPagador]
            ]
        )->once();

        $this->carteiraRepository->shouldReceive('atualizar')->withArgs(
            [
                $carteiraBeneficiario->id,
                ['saldo' => $novoSaldoBeneficiario]
            ]
        )->once();

        $this->repository->shouldReceive('atualizar')->withArgs(
            [
                $transferencias->first()->id,
                ['status' => config('constants.status_transferencias.efetivada')]
            ]
        )->once();

        $this->service->efetivar();
    }

    /**
     * @test
     */
    public function naoDeveEfetivarTransferenciaQuandoAutorizacaoForRecusada()
    {
        $headers  = ['Content-Type' => 'application/json'];
        $dados    = json_encode(['message' => self::TRANSFERENCIA_RECUSADA]);
        $resposta = new GuzzleResponse(Response::HTTP_OK, $headers, $dados);

        $transferencias              = Transferencia::factory(1)->make();
        $transferencias->first()->id = $this->faker->randomDigitNotZero();

        $this->repository->shouldReceive('obterTransferenciasPorStatus')->with(config('constants.status_transferencias.nao_efetivada'))->once()->andReturn($transferencias);

        $this->guzzleClient->shouldReceive('request')->withArgs(
            [
                'get',
                config('services.transference.authorize')
            ]
        )->andReturn($resposta);

        $this->repository->shouldReceive('atualizar')->withArgs(
            [
                $transferencias->first()->id,
                ['status' => config('constants.status_transferencias.recusada')]
            ]
        )->once();

        $this->service->efetivar();
    }

    /**
     * @test
     */
    public function naoDeveEfetivarTransferenciaQuandoComunicacaoComServicoFalhar()
    {
        $mockRequisicao = \Mockery::mock(RequestInterface::class);
        $mockResposta   = \Mockery::mock(ResponseInterface::class);

        $mockResposta->allows('getStatusCode');

        $transferencias = Transferencia::factory(1)->make();

        $this->repository->shouldReceive('obterTransferenciasPorStatus')->with(config('constants.status_transferencias.nao_efetivada'))->once()->andReturn($transferencias);

        $this->guzzleClient->shouldReceive('request')->withArgs(
            [
                'get',
                config('services.transference.authorize')
            ]
        )->andThrow(new ClientException('Ocorreu um erro!', $mockRequisicao, $mockResposta));

        $this->service->efetivar();
    }
}