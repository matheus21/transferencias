<?php

namespace Database\Factories\Domain\Models;

use App\Domain\Models\Pessoa;
use Faker\Provider\pt_BR\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaFactory extends Factory
{
    protected $model = Pessoa::class;

    public function definition(): array
    {
        $this->faker->addProvider(new Person($this->faker));

        return [
            'cpf_cnpj'    => $this->faker->cpf(false),
            'nome'        => $this->faker->name,
            'email'       => $this->faker->unique()->email,
            'senha'       => $this->faker->password,
            'tipo_pessoa' => config('constants.tipos_pessoas.pessoa_fisica')
        ];
    }
}