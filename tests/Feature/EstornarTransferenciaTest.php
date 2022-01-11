<?php

namespace Tests\Feature;

use App\Domain\Models\Carteira;
use App\Domain\Models\Pessoa;
use App\Domain\Models\Transferencia;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;

class EstornarTransferenciaTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function deveEstornarUmaTransferencia()
    {
        $pessoaPagadora     = Pessoa::factory()->create();
        $pessoaBeneficiaria = Pessoa::factory()->create();

        $carteiraPagador      = Carteira::factory()->create(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraBeneficiario = Carteira::factory()->create(['pessoa_id' => $pessoaBeneficiaria->id]);

        $valorTransferencia = $this->faker->randomFloat(2, 1, $carteiraBeneficiario->saldo);

        $transferencia = Transferencia::factory()->create(
            [
                'carteira_pagador_id'      => $carteiraPagador->id,
                'carteira_beneficiario_id' => $carteiraBeneficiario->id,
                'valor'                    => $valorTransferencia,
                'status'                   => config('constants.status_transferencias.efetivada')
            ]
        );

        $retorno = $this->put('/api/transferencia/estornar/' . $transferencia->id);

        $retorno->assertStatus(Response::HTTP_OK);
        $this->assertArrayHasKey('mensagem', $retorno->getOriginalContent());
        $this->assertArrayHasKey('dados', $retorno->getOriginalContent());
        $this->assertEquals(
            $retorno->getOriginalContent()['mensagem'], trans('messages.transferences.success.reversal')
        );
    }

    /**
     * @test
     */
    public function deveFalharEstornarUmaTransferenciaNaoExistente()
    {
        $retorno = $this->put('/api/transferencia/estornar/' . $this->faker->randomDigitNotZero());

        $retorno->assertStatus(Response::HTTP_NOT_FOUND);
        $this->assertArrayHasKey('mensagem', $retorno->getOriginalContent());
        $this->assertEquals(
            $retorno->getOriginalContent()['mensagem'], trans('exception.transference.not_found')
        );
    }

    /**
     * @test
     */
    public function deveFalharAoEstornarUmaTransferenciaNaoEfetivada()
    {
        $pessoaPagadora     = Pessoa::factory()->create();
        $pessoaBeneficiaria = Pessoa::factory()->create();

        $carteiraPagador      = Carteira::factory()->create(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraBeneficiario = Carteira::factory()->create(['pessoa_id' => $pessoaBeneficiaria->id]);

        $transferencia = Transferencia::factory()->create(
            [
                'carteira_pagador_id'      => $carteiraPagador->id,
                'carteira_beneficiario_id' => $carteiraBeneficiario->id,
                'status'                   => config('constants.status_transferencias.nao_efetivada')
            ]
        );

        $retorno = $this->put('/api/transferencia/estornar/' . $transferencia->id);

        $retorno->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('mensagem', $retorno->getOriginalContent());
        $this->assertEquals(
            $retorno->getOriginalContent()['mensagem'], trans('exception.transference.reversal_not_allowed')
        );
    }
}