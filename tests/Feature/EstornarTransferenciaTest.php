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

        $this->put('/api/transferencia/estornar/' . $transferencia->id)->assertStatus(Response::HTTP_OK);
    }
}