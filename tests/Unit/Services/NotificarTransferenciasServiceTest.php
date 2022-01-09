<?php

namespace Tests\Unit\Services;

use App\Domain\Models\Transferencia;
use App\Domain\Services\Contracts\NotificarTransferencias;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

class NotificarTransferenciasServiceTest extends TestCase
{
    const NOTIFICACAO_ENVIADA = "Success";

    private NotificarTransferencias $service;
    private TransferenciaRepositoryInterface $repository;
    private ClientInterface $guzzleClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository   = \Mockery::mock(TransferenciaRepositoryInterface::class);
        $this->guzzleClient = \Mockery::mock(ClientInterface::class);

        $this->app->instance(TransferenciaRepositoryInterface::class, $this->repository);
        $this->app->instance(ClientInterface::class, $this->guzzleClient);

        $this->service = $this->app->make(NotificarTransferencias::class);
    }

    /**
     * @test
     */
    public function deveNotificarTransferencias()
    {
        $headers  = ['Content-Type' => 'application/json'];
        $dados    = json_encode(['message' => self::NOTIFICACAO_ENVIADA]);
        $resposta = new GuzzleResponse(Response::HTTP_OK, $headers, $dados);

        $transferencias              = Transferencia::factory(1)->make();
        $transferencias->first()->id = $this->faker->randomDigitNotZero();
        $filtros                     = [
            'status'              => config('constants.status_transferencias.efetivada'),
            'notificacao_enviada' => false
        ];

        $this->repository->shouldReceive('obterTransferenciasPorFiltros')->with($filtros)->once()->andReturn($transferencias);

        $this->guzzleClient->shouldReceive('request')->withArgs(
            [
                'get',
                config('services.transference.notify')
            ]
        )->andReturn($resposta);

        $this->repository->shouldReceive('atualizar')->withArgs(
            [
                $transferencias->first()->id,
                ['notificacao_enviada' => true]
            ]
        )->once();

        $this->service->notificar();
    }

    /**
     * @test
     */
    public function naoDeveNotificarTransferenciaQuandoComunicacaoComServicoFalhar()
    {
        $mockRequisicao = \Mockery::mock(RequestInterface::class);
        $mockResposta   = \Mockery::mock(ResponseInterface::class);

        $mockResposta->allows('getStatusCode');

        $transferencias              = Transferencia::factory(1)->make();
        $transferencias->first()->id = $this->faker->randomDigitNotZero();
        $filtros                     = [
            'status'              => config('constants.status_transferencias.efetivada'),
            'notificacao_enviada' => false
        ];

        $this->repository->shouldReceive('obterTransferenciasPorFiltros')->with($filtros)->once()->andReturn($transferencias);

        $this->guzzleClient->shouldReceive('request')->withArgs(
            [
                'get',
                config('services.transference.notify')
            ]
        )->andThrow(new ClientException('Ocorreu um erro!', $mockRequisicao, $mockResposta));

        $this->service->notificar();
    }
}