<?php

namespace Database\Seeders;

use App\Domain\Models\Carteira;
use App\Domain\Models\Pessoa;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Faker::create('pt_BR');
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $pessoaFisica = Pessoa::factory()->create();
        Carteira::factory()->create(
            [
                'pessoa_id' => $pessoaFisica->id,
                'saldo'     => $this->faker->randomFloat(2, 100, 9999.99)
            ]
        );

        $outraPessoaFisica = Pessoa::factory()->create();
        Carteira::factory()->create(
            [
                'pessoa_id' => $outraPessoaFisica->id,
                'saldo'     => $this->faker->randomFloat(2, 100, 9999.99)
            ]
        );

        $pessoaJuridica = Pessoa::factory()->create(
            [
                'nome'        => $this->faker->company,
                'cpf_cnpj'    => $this->faker->cnpj(false),
                'tipo_pessoa' => config('constants.tipos_pessoas.pessoa_juridica'),
                'email'       => $this->faker->companyEmail
            ]
        );
        Carteira::factory()->create(
            [
                'pessoa_id' => $pessoaJuridica->id,
                'saldo'     => $this->faker->randomFloat(2, 100, 9999.99)
            ]
        );
    }
}
