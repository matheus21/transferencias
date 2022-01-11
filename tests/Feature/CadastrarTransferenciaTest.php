<?php

namespace Tests\Feature;

use App\Domain\Models\Carteira;
use App\Domain\Models\Pessoa;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        $retorno = $this->post('/api/transferencia/cadastrar', $dados);

        $retorno->assertStatus(Response::HTTP_CREATED);
        $this->assertArrayHasKey('mensagem', $retorno->getOriginalContent());
        $this->assertArrayHasKey('dados', $retorno->getOriginalContent());
        $this->assertEquals(
            $retorno->getOriginalContent()['mensagem'], trans('messages.transferences.success.registered')
        );
    }

    /**
     * @test
     */
    public function deveFalharAoCadastrarUmaTransferenciaQuandoSaldoForInsuficiente()
    {
        $pessoaPagadora     = Pessoa::factory()->create();
        $pessoaBeneficiaria = Pessoa::factory()->create();

        $carteiraPagador      = Carteira::factory()->create(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraBeneficiario = Carteira::factory()->create(['pessoa_id' => $pessoaBeneficiaria->id]);

        $dados = [
            'valor'        => $carteiraPagador->saldo + 1,
            'pagador'      => $carteiraPagador->id,
            'beneficiario' => $carteiraBeneficiario->id
        ];

        $retorno = $this->post('/api/transferencia/cadastrar', $dados);

        $retorno->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('mensagem', $retorno->getOriginalContent());
        $this->assertEquals(
            $retorno->getOriginalContent()['mensagem'], trans('exception.transference.insufficient_funds')
        );
    }

    /**
     * @test
     */
    public function deveFalharAoCadastrarUmaTransferenciaQuandoPagadorForPessoaJuridica()
    {
        $pessoaPagadora     = Pessoa::factory()->create(
            [
                'tipo_pessoa' => config('constants.tipos_pessoas.pessoa_juridica'),
                'cpf_cnpj'    => $this->faker->cnpj(false)
            ]
        );
        $pessoaBeneficiaria = Pessoa::factory()->create();

        $carteiraPagador      = Carteira::factory()->create(['pessoa_id' => $pessoaPagadora->id]);
        $carteiraBeneficiario = Carteira::factory()->create(['pessoa_id' => $pessoaBeneficiaria->id]);

        $dados = [
            'valor'        => $carteiraPagador->saldo,
            'pagador'      => $carteiraPagador->id,
            'beneficiario' => $carteiraBeneficiario->id
        ];

        $retorno = $this->post('/api/transferencia/cadastrar', $dados);

        $retorno->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertArrayHasKey('mensagem', $retorno->getOriginalContent());
        $this->assertEquals(
            $retorno->getOriginalContent()['mensagem'], trans('exception.transference.not_allowed')
        );
    }


    /**
     * @test
     * @dataProvider dadosParaCadastrar
     */
    public function deveFalharAoCadastrarQuandoDadosEnviadosForemInvalidos($dados)
    {
        $retorno = $this->post('/api/transferencia/cadastrar', $dados);

        $retorno->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('mensagem', $retorno->getOriginalContent());
        $this->assertArrayHasKey('erros', $retorno->getOriginalContent());
        $this->assertEquals(
            $retorno->getOriginalContent()['mensagem'], trans('validation.invalid')
        );
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