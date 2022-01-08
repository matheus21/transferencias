<?php

namespace Tests\Feature;

use App\Domain\Models\Carteira;
use App\Domain\Models\Pessoa;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;

class CadastrarTransferenciaTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function deveCadastrarUmaTransferencia()
    {
        $pessoaPagadora     = Pessoa::factory()->create();
        $pessoaBeneficiaria = Pessoa::factory()->create();

        $carteiraPagador      = Carteira::factory()->create(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraBeneficiario = Carteira::factory()->create(['pessoa_id' => $pessoaBeneficiaria->id]);

        $valorTransferencia = $this->faker->randomFloat(2, 1, $carteiraPagador->saldo);

        $dados = [
            'valor'        => $valorTransferencia,
            'pagador'      => $carteiraPagador->id,
            'beneficiario' => $carteiraBeneficiario->id
        ];

        $this->post('/api/transferencia/cadastrar', $dados)->assertStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     * @dataProvider dadosParaCadastrar
     */
    public function deveFalharAoCadastrarQuandoDadosEnviadosForemInvalidos($dados)
    {
        $this->post('/api/transferencia/cadastrar', $dados)->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function dadosParaCadastrar()
    {
        $faker = Faker::create();
        $id    = $faker->randomDigitNotZero();

        return [
            [
                [
                    'valor'        => $faker->randomFloat(2, 1, 9999.99),
                    'pagador'      => $faker->randomDigitNotZero(),
                    'beneficiario' => $faker->randomDigitNotZero()
                ]
            ],
            [
                [
                    'valor'        => $faker->randomFloat(3, 0.001, 0.009),
                    'pagador'      => $faker->randomDigitNotZero(),
                    'beneficiario' => $faker->randomDigitNotZero()
                ]
            ],
            [
                [
                    'valor'        => $faker->randomFloat(2, 1, 9999.99),
                    'pagador'      => $id,
                    'beneficiario' => $id
                ]
            ]
        ];
    }
}