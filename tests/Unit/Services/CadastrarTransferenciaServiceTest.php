<?php

namespace Tests\Unit\Services;

use App\Domain\Models\Carteira;
use App\Domain\Models\Pessoa;
use App\Domain\Models\Transferencia;
use App\Domain\Services\Contracts\CadastrarTransferencia;
use App\Repositories\Contracts\CarteiraRepositoryInteface;
use App\Repositories\Contracts\PessoaRepositoryInterface;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class CadastrarTransferenciaServiceTest extends TestCase
{
    private CadastrarTransferencia $service;
    private TransferenciaRepositoryInterface $repository;
    private CarteiraRepositoryInteface $carteiraRepository;
    private PessoaRepositoryInterface $pessoaRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository         = \Mockery::mock(TransferenciaRepositoryInterface::class);
        $this->carteiraRepository = \Mockery::mock(CarteiraRepositoryInteface::class);
        $this->pessoaRepository   = \Mockery::mock(PessoaRepositoryInterface::class);

        $this->app->instance(TransferenciaRepositoryInterface::class, $this->repository);
        $this->app->instance(CarteiraRepositoryInteface::class, $this->carteiraRepository);
        $this->app->instance(PessoaRepositoryInterface::class, $this->pessoaRepository);

        $this->service = $this->app->make(CadastrarTransferencia::class);
    }

    /**
     * @test
     */
    public function deveCadastrarUmaTransferencia()
    {
        $pessoaPagadora     = Pessoa::factory()->make();
        $pessoaPagadora->id = $this->faker->randomDigitNotZero();

        $carteiraPagador     = Carteira::factory()->make(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraPagador->id = $this->faker->randomDigitNotZero();

        $dados = [
            'pagador'      => $carteiraPagador->id,
            'beneficiario' => $this->faker->randomDigitNotZero(),
            'valor'        => $this->faker->randomFloat(2, 1, $carteiraPagador->saldo)
        ];

        $dadosParaInserir = [
            'carteira_pagador_id'      => $carteiraPagador->id,
            'carteira_beneficiario_id' => $dados['beneficiario'],
            'valor'                    => $dados['valor'],
            'status'                   => config('constants.status_transferencias.nao_efetivada')
        ];

        $transferenciaInserida     = Transferencia::factory()->make($dadosParaInserir);
        $transferenciaInserida->id = $this->faker->randomDigitNotZero();

        $this->carteiraRepository->shouldReceive('obter')->with($carteiraPagador->id)->once()->andReturn($carteiraPagador);
        $this->pessoaRepository->shouldReceive('obter')->with($pessoaPagadora->id)->once()->andReturn($pessoaPagadora);
        $this->repository->shouldReceive('obterTransferenciasNaoEfetivadasPorPagador')->with($carteiraPagador->id)->once()->andReturn(new Collection());
        $this->repository->shouldReceive('inserir')->with($dadosParaInserir)->andReturn($transferenciaInserida);

        $retorno = $this->service->cadastrar($dados);
        $this->assertEquals($retorno, $transferenciaInserida->toArray());
    }

    /**
     * @test
     */
    public function deveCadastrarUmaTransferenciaComValorIgualAoSaldoDaCarteira()
    {
        $pessoaPagadora     = Pessoa::factory()->make();
        $pessoaPagadora->id = $this->faker->randomDigitNotZero();

        $carteiraPagador     = Carteira::factory()->make(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraPagador->id = $this->faker->randomDigitNotZero();

        $dados = [
            'pagador'      => $carteiraPagador->id,
            'beneficiario' => $this->faker->randomDigitNotZero(),
            'valor'        => $carteiraPagador->saldo
        ];

        $dadosParaInserir = [
            'carteira_pagador_id'      => $carteiraPagador->id,
            'carteira_beneficiario_id' => $dados['beneficiario'],
            'valor'                    => $dados['valor'],
            'status'                   => config('constants.status_transferencias.nao_efetivada')
        ];

        $transferenciaInserida     = Transferencia::factory()->make($dadosParaInserir);
        $transferenciaInserida->id = $this->faker->randomDigitNotZero();

        $this->carteiraRepository->shouldReceive('obter')->with($carteiraPagador->id)->once()->andReturn($carteiraPagador);
        $this->pessoaRepository->shouldReceive('obter')->with($pessoaPagadora->id)->once()->andReturn($pessoaPagadora);
        $this->repository->shouldReceive('obterTransferenciasNaoEfetivadasPorPagador')->with($carteiraPagador->id)->once()->andReturn(new Collection());
        $this->repository->shouldReceive('inserir')->with($dadosParaInserir)->andReturn($transferenciaInserida);

        $retorno = $this->service->cadastrar($dados);
        $this->assertEquals($retorno, $transferenciaInserida->toArray());
    }

    /**
     * @test
     */
    public function deveFalharAoCadastrarTransferenciaComPagadorPessoaJuridica()
    {
        $pessoaPagadora     = Pessoa::factory()->make(
            [
                'tipo_pessoa' => config('constants.tipos_pessoas.pessoa_juridica'),
                'cpf_cnpj'    => $this->faker->cnpj(false)
            ]
        );
        $pessoaPagadora->id = $this->faker->randomDigitNotZero();

        $carteiraPagador     = Carteira::factory()->make(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraPagador->id = $this->faker->randomDigitNotZero();

        $dados = [
            'pagador'      => $carteiraPagador->id,
            'beneficiario' => $this->faker->randomDigitNotZero(),
            'valor'        => $this->faker->randomFloat(2, 1, $carteiraPagador->saldo)
        ];

        $this->carteiraRepository->shouldReceive('obter')->with($carteiraPagador->id)->once()->andReturn($carteiraPagador);
        $this->pessoaRepository->shouldReceive('obter')->with($pessoaPagadora->id)->once()->andReturn($pessoaPagadora);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage(trans('exception.transference.not_allowed'));

        $this->service->cadastrar($dados);
    }

    /**
     * @test
     * @dataProvider transferenciasNaoEfetivadas
     */
    public function deveFalharAoCadastrarQuandoPagadorNaoPossuirSaldoSuficiente($transferencias)
    {
        $pessoaPagadora     = Pessoa::factory()->make();
        $pessoaPagadora->id = $this->faker->randomDigitNotZero();

        $carteiraPagador     = Carteira::factory()->make(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraPagador->id = $this->faker->randomDigitNotZero();

        $dados = [
            'pagador'      => $carteiraPagador->id,
            'beneficiario' => $this->faker->randomDigitNotZero(),
            'valor'        => $this->faker->randomFloat(2, $carteiraPagador->saldo + 1, 9999.99)
        ];

        $this->carteiraRepository->shouldReceive('obter')->with($carteiraPagador->id)->once()->andReturn($carteiraPagador);
        $this->pessoaRepository->shouldReceive('obter')->with($pessoaPagadora->id)->once()->andReturn($pessoaPagadora);
        $this->repository->shouldReceive('obterTransferenciasNaoEfetivadasPorPagador')->with($carteiraPagador->id)->once()->andReturn($transferencias);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage(trans('exception.transference.insufficient_funds'));

        $this->service->cadastrar($dados);
    }

    public function transferenciasNaoEfetivadas(): array
    {
        $this->createApplication();
        $collection    = new Collection();
        $transferencia = Transferencia::factory()->make();


        return [
            [$collection],
            [$collection->push($transferencia)],
        ];
    }
}