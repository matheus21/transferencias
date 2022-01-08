<?php

namespace Database\Factories\Domain\Models;

use App\Domain\Models\Carteira;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarteiraFactory extends Factory
{
    protected $model = Carteira::class;

    public function definition(): array
    {
        return [
            'pessoa_id' => null,
            'saldo'     => $this->faker->randomFloat(2, 1, 9999.99)
        ];
    }
}