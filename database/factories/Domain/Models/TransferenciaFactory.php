<?php

namespace Database\Factories\Domain\Models;

use App\Domain\Models\Transferencia;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransferenciaFactory extends Factory
{
    protected $model = Transferencia::class;

    public function definition(): array
    {
        return [
            'carteira_pagador_id'      => null,
            'carteira_beneficiario_id' => null,
            'valor'                    => $this->faker->randomFloat(2, 1, 9999.99),
            'notificacao_enviada'      => false,
            'status'                   => config('constants.status_transferencias.nao_efetivada')
        ];
    }
}