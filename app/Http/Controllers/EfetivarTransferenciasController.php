<?php

namespace App\Http\Controllers;

use App\Domain\Services\Contracts\EfetivarTransferencias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EfetivarTransferenciasController extends Controller
{
    private EfetivarTransferencias $service;
    private DB $database;

    /**
     * @param EfetivarTransferencias $service
     * @param DB $database
     */
    public function __construct(EfetivarTransferencias $service, DB $database)
    {
        $this->service  = $service;
        $this->database = $database;
    }

    /**
     * @OA\Post(
     *     path="/api/transferencia/efetivar",
     *     operationId="EfetivarTransferencias",
     *     tags={"Transferências"},
     *     summary="Efetivar transferências",
     *     description="Efetiva todas as transferências cadastradas",
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
            $this->database::beginTransaction();
            $this->service->efetivar();
            $this->database::commit();

            return new JsonResponse(
                ['mensagem' => trans('messages.transferences.success.effected')],
                Response::HTTP_OK
            );
        } catch (HttpException $e) {
            $this->database::rollBack();

            return new JsonResponse(['mensagem' => $e->getMessage()], $e->getStatusCode());
        }
    }
}