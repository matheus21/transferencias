<?php

namespace App\Http\Controllers;

use App\Domain\Services\Contracts\EstornarTransferencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EstornarTransferenciaController extends Controller
{
    private EstornarTransferencia $service;

    /**
     * @param EstornarTransferencia $service
     */
    public function __construct(EstornarTransferencia $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Put(
     *     path="/api/transferencia/estornar/{id}",
     *     operationId="EstornarTransferencia",
     *     tags={"Transferências"},
     *     summary="Estornar transferência",
     *     description="Realiza o estorno de uma transferência, criando uma nova do beneficiário para o pagador.",
     *     @OA\Parameter(
     *         description="Identificador da transferência",
     *         in="path",
     *         name="id",
     *         required=false,
     *         example="1",
     *         @OA\Schema (
     *             type="int"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso ao estornar",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="mensagem", type="string", example="Transferência estornada com sucesso!"
     *             ),
     *             @OA\Property(
     *                 property="dados", type="object",
     *                 @OA\Property(property="carteira_pagador_id", type="int", example="2",
     *                     description="Número identificador da carteira do pagador"
     *                 ),
     *                 @OA\Property(property="carteira_beneficiario_id", type="int", example="1",
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
     *                 @OA\Property(property="id", type="int", example="2",
     *                     description="Número identificador da transferência"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Transferência não efetivada",
     *         @OA\JsonContent(
     *             @OA\Examples(example="nao_efetivada", value={"mensagem": "Só podem ser estornadas transferências efetivadas."}, summary="Transferência não efetivada"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Pagador pessoa jurídica",
     *         @OA\JsonContent(
     *             @OA\Examples(example="pessoa_fisica", value={"mensagem": "Apenas pessoas físicas podem realizar transferências."}, summary="Transferência com pessoa jurídica")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transferência não encontrada",
     *         @OA\JsonContent(
     *             @OA\Examples(example="nao_encontrada", value={"mensagem": "Transferência não encontrada."}, summary="Transferência não encontrada")
     *         ),
     *     ),
     * )
     */
    public function estornar(int $idTransferencia): JsonResponse
    {
        try {
            $dados = $this->service->estornar($idTransferencia);

            return new JsonResponse(
                ['mensagem' => trans('messages.transferences.success.reversal'), 'dados' => $dados],
                Response::HTTP_OK
            );
        } catch (HttpException $e) {
            return new JsonResponse(['mensagem' => $e->getMessage()], $e->getStatusCode());
        }
    }
}