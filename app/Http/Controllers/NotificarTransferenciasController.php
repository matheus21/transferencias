<?php

namespace App\Http\Controllers;

use App\Domain\Services\Contracts\NotificarTransferencias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotificarTransferenciasController extends Controller
{
    private NotificarTransferencias $service;

    /**
     * @param NotificarTransferencias $service
     */
    public function __construct(NotificarTransferencias $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *     path="/api/transferencia/notificar",
     *     operationId="NotiicarTransferencia",
     *     tags={"Transferências"},
     *     summary="Notificar transferências",
     *     description="Notifica todas as transferências efetivadas e ainda não notificadas.",
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
            $this->service->notificar();

            return new JsonResponse(
                ['mensagem' => trans('messages.transferences.success.notified')],
                Response::HTTP_OK
            );
        } catch (HttpException $e) {
            return new JsonResponse(['mensagem' => $e->getMessage()], $e->getStatusCode());
        }
    }

}