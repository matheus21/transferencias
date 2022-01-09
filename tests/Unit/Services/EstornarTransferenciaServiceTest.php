<?php

namespace Tests\Unit\Services;

use App\Domain\Models\Transferencia;
use App\Domain\Services\Contracts\CadastrarTransferencia;
use App\Domain\Services\Contracts\EstornarTransferencia;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class EstornarTransferenciaServiceTest extends TestCase
{
    private EstornarTransferencia $service;
    private TransferenciaRepositoryInterface $repository;
    private CadastrarTransferencia $cadastrarService;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository       = \Mockery::mock(TransferenciaRepositoryInterface::class);
        $this->cadastrarService = \Mockery::mock(CadastrarTransferencia::class);

        $this->app->instance(TransferenciaRepositoryInterface::class, $this->repository);
        $this->app->instance(CadastrarTransferencia::class, $this->cadastrarService);

        $this->service = $this->app->make(EstornarTransferencia::class);
    }

    /**
     * @test
     */
    public function deveEstornarUmaTransferencia()
    {
        $dados = [
            'carteira_pagador_id'      => $this->faker->randomDigitNotZero(),
            'carteira_beneficiario_id' => $this->faker->randomDigitNotZero(),
            'status'                   => config('constants.status_transferencias.efetivada')
        ];

        $transferencia     = Transferencia::factory()->make($dados);
        $transferencia->id = $this->faker->randomDigitNotZero();

        $dadosParaInserir = [
            'valor'        => $transferencia->valor,
            'pagador'      => $transferencia->carteira_beneficiario_id,
            'beneficiario' => $transferencia->carteira_pagador_id
        ];

        $this->repository->shouldReceive('obter')->with($transferencia->id)->once()->andReturn($transferencia);
        $this->cadastrarService->shouldReceive('cadastrar')->with($dadosParaInserir)->once()->andReturn($transferencia->toArray());
        $this->repository->shouldReceive('atualizar')->withArgs(
            [
                $transferencia->id,
                ['status' => config('constants.status_transferencias.estornada')]
            ]
        )->once();

        $retorno = $this->service->estornar($transferencia->id);
        $this->assertEquals($retorno, $transferencia->toArray());
    }

    /**
     * @test
     */
    public function deveFalharAoTentarEstornarTransferenciaInexistente()
    {
        $idTransferencia = $this->faker->randomDigitNotZero();
        $this->repository->shouldReceive('obter')->with($idTransferencia)->once()->andReturnNull();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage(trans('exception.transference.not_found'));

        $this->service->estornar($idTransferencia);
    }

    /**
     * @test
     * @dataProvider statusTransferencias
     */
    public function deveFalharAoTentarEstornarTransferenciaNaoEfetivada($status)
    {
        $transferencia     = Transferencia::factory()->make(['status' => $status]);
        $transferencia->id = $this->faker->randomDigitNotZero();

        $this->repository->shouldReceive('obter')->with($transferencia->id)->once()->andReturn($transferencia);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage(trans('exception.transference.reversal_not_allowed'));

        $this->service->estornar($transferencia->id);
    }

    public function statusTransferencias(): array
    {
        $this->createApplication();

        return [
            [config('constants.status_transferencias.nao_efetivada')],
            [config('constants.status_transferencias.estornada')],
            [config('constants.status_transferencias.recusada')],
        ];
    }
}