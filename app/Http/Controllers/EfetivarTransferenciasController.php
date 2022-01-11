<?php

namespace App\Http\Controllers;

use App\Domain\Services\Contracts\EfetivarTransferencias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EfetivarTransferenciasController extends Controller
{
    private EfetivarTransferencias $service;

    /**
     * @param EfetivarTransferencias $service
     */
    public function __construct(EfetivarTransferencias $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *     path="/api/transferencia/efetivar",
     *     operationId="EfetivarTransferencias",
     *     tags={"Transferências"},
     *     summary="Efetivar transferências",
     *     description="Efetiva todas as transferências cadastradas e atualiza o saldo das carteiras.",
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso ao efetivar",
     *         @OA\JsonContent(
     *             @OA\Examples(example="sucesso", value={"mensagem": "Transferências efetivadas com sucesso!"}, summary="Transferencias efetivadas")
     *         ),
     *     ),
     * )
     */
    public function efetivar(): JsonResponse
    {
        try {
            $this->service->efetivar();

            return new JsonResponse(
                ['mensagem' => trans('messages.transferences.success.effected')],
                Response::HTTP_OK
            );
        } catch (HttpException $e) {
            return new JsonResponse(['mensagem' => $e->getMessage()], $e->getStatusCode());
        }
    }
}