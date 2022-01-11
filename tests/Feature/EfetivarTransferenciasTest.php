<?php

namespace Tests\Feature;

use App\Domain\Models\Carteira;
use App\Domain\Models\Pessoa;
use App\Domain\Models\Transferencia;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;

class EfetivarTransferenciasTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function deveEfetivarTransferencias()
    {
        $pessoaPagadora     = Pessoa::factory()->create();
        $pessoaBeneficiaria = Pessoa::factory()->create();

        $carteiraPagador      = Carteira::factory()->create(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraBeneficiario = Carteira::factory()->create(['pessoa_id' => $pessoaBeneficiaria->id]);

        $valorTransferencia = $this->faker->randomFloat(2, 1, $carteiraPagador->saldo);

        Transferencia::factory()->create(
            [
                'carteira_pagador_id'      => $carteiraPagador->id,
                'carteira_beneficiario_id' => $carteiraBeneficiario->id,
                'valor'                    => $valorTransferencia
            ]
        );

        $retorno = $this->post('/api/transferencia/efetivar');

        $retorno->assertStatus(Response::HTTP_OK);
        $this->assertArrayHasKey('mensagem', $retorno->getOriginalContent());
        $this->assertEquals(
            $retorno->getOriginalContent()['mensagem'], trans('messages.transferences.success.effected')
        );
    }
}