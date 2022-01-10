<?php

namespace App\Http\Controllers;

use App\Domain\Services\Contracts\CadastrarTransferencia;
use App\Http\Requests\CadastrarTransferenciaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CadastrarTransferenciaController extends Controller
{
    private CadastrarTransferencia $service;
    private DB $database;

    /**
     * @param CadastrarTransferencia $service
     * @param DB $database
     */
    public function __construct(CadastrarTransferencia $service, DB $database)
    {
        $this->service  = $service;
        $this->database = $database;
    }


    /**
     * @OA\Post(
     *     path="/api/transferencia/cadastrar",
     *     operationId="CadastrarTransferencia",
     *     tags={"Transferências"},
     *     summary="Cadastrar transferência",
     *     description="Cadastra uma nova transferência entre pessoas.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"valor", "pagador", "beneficiario"},
     *             @OA\Property(
     *                 property="valor", type="float", example="21.17", description="Valor a ser transferido"
     *             ),
     *             @OA\Property(
     *                 property="pagador", type="int", example="1", description="Número identificador da carteira do pagador"
     *             ),
     *             @OA\Property(
     *                 property="beneficiario", type="int", example="2", description="Número identificador da carteira do beneficiário"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201
     *     ,
     *         description="Sucesso ao cadastrar",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="mensagem", type="string", example="Transferência cadastrada com sucesso!"
     *             ),
     *             @OA\Property(
     *                 property="dados", type="object",
     *                 @OA\Property(property="carteira_pagador_id", type="int", example="1",
     *                     description="Número identificador da carteira do pagador"
     *                 ),
     *                 @OA\Property(property="carteira_beneficiario_id", type="int", example="2",
     *                     description="Número indentificador da carteira do beneficiário"
     *                 ),
     *                 @OA\Property(property="valor", type="float", example="21.17",
     *                     description="Valor a ser transferido"
     *                 ),
     *                 @OA\Property(
     *                     property="status", type="int", example="3",
     *                     description="Status da transferência. 1: Efetivada, 2: Recusada, 3: Não efetivada, 4: Estornada."
     *                 ),
     *                 @OA\Property(
     *                     property="created_at", type="datetime", example="2021-12-30T02:06:16.000000Z",
     *                     description="Data em que a transferência foi cadastrada."
     *                 ),
     *                 @OA\Property(
     *                     property="updated_at", type="datetime", example="2021-12-30T02:06:16.000000Z",
     *                     description="Data em que a transferência foi atualizada."
     *                 ),
     *                 @OA\Property(property="id", type="int", example="1",
     *                     description="Número identificador da transferência"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erros de validação",
     *         @OA\JsonContent(
     *             @OA\Examples(example="request", value={"mensagem":"Os dados enviados são inválidos.","erros":{"pagador":{"pagador e beneficiario devem ser diferentes."},"valor":{"valor deve ser no mímimo 0.01."}}}, summary="Dados inválidos"),
     *             @OA\Examples(example="saldo", value={"mensagem": "Pagador não possui saldo suficiente."}, summary="Saldo insuficiente")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Pagador pessoa jurídica",
     *         @OA\JsonContent(
     *             @OA\Examples(example="pessoa_fisica", value={"mensagem": "Apenas pessoas físicas podem realizar transferências."}, summary="Transferência com pessoa jurídica")
     *         ),
     *     ),
     * )
     */
    public function cadastrar(CadastrarTransferenciaRequest $request): JsonResponse
    {
        try {
            $this->database::beginTransaction();
            $dados = $this->service->cadastrar($request->all());
            $this->database::commit();

            return new JsonResponse(
                ['mensagem' => trans('messages.transferences.success.registered'), 'dados' => $dados],
                Response::HTTP_CREATED
            );
        } catch (HttpException $e) {
            $this->database::rollBack();

            return new JsonResponse(['mensagem' => $e->getMessage()], $e->getStatusCode());
        }
    }
}