<?php

namespace App\Http\Controllers;

use App\Domain\Services\Contracts\EstornarTransferencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EstornarTransferenciaController extends Controller
{
    private EstornarTransferencia $service;
    private DB $database;

    /**
     * @param EstornarTransferencia $service
     * @param DB $database
     */
    public function __construct(EstornarTransferencia $service, DB $database)
    {
        $this->service  = $service;
        $this->database = $database;
    }

    /**
     * @OA\Put(
     *     path="/api/transferencia/estornar/{id}",
     *     operationId="EstornarTransferencia",
     *     tags={"Transferências"},
     *     summary="Estornar transferência",
     *     description="Realiza o estorno de uma transferência",
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
            $this->database::beginTransaction();
            $dados = $this->service->estornar($idTransferencia);
            $this->database::commit();

            return new JsonResponse(
                ['mensagem' => trans('messages.transferences.success.reversal'), 'dados' => $dados],
                Response::HTTP_OK
            );
        } catch (HttpException $e) {
            $this->database::rollBack();

            return new JsonResponse(['mensagem' => $e->getMessage()], $e->getStatusCode());
        }
    }
}