<?php

namespace App\Http\Controllers;

use App\Domain\Services\Contracts\NotificarTransferencias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotificarTransferenciasController extends Controller
{
    private NotificarTransferencias $service;
    private DB $database;

    /**
     * @param NotificarTransferencias $service
     * @param DB $database
     */
    public function __construct(NotificarTransferencias $service, DB $database)
    {
        $this->service  = $service;
        $this->database = $database;
    }


    /**
     * @OA\Post(
     *     path="/api/transferencia/notificar",
     *     operationId="NotiicarTransferencia",
     *     tags={"Transferências"},
     *     summary="Notificar transferências",
     *     description="Notifica todas as transferências efetivadas",
     *     @OA\Response(
     *         response=200,
     *         description="Sucesso ao notificar",
     *         @OA\JsonContent(
     *             @OA\Examples(example="sucesso", value={"mensagem": "Transferências notificadas com sucesso!"}, summary="Transferencias notificadas")
     *         ),
     *     ),
     * )
     */
    public function notificar(): JsonResponse
    {
        try {
            $this->database::beginTransaction();
            $this->service->notificar();
            $this->database::commit();

            return new JsonResponse(
                ['mensagem' => trans('messages.transferences.success.notified')],
                Response::HTTP_OK
            );
        } catch (HttpException $e) {
            $this->database::rollBack();

            return new JsonResponse(['mensagem' => $e->getMessage()], $e->getStatusCode());
        }
    }

}