<?php

use App\Http\Controllers\CadastrarTransferenciaController;
use App\Http\Controllers\EfetivarTransferenciasController;
use App\Http\Controllers\EstornarTransferenciaController;
use App\Http\Controllers\NotificarTransferenciasController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'transferencia'], function () {
    Route::post('/cadastrar', [CadastrarTransferenciaController::class, 'cadastrar']);
    Route::post('/efetivar', [EfetivarTransferenciasController::class, 'efetivar']);
    Route::put('/estornar/{id}', [EstornarTransferenciaController::class, 'estornar']);
    Route::post('/notificar', [NotificarTransferenciasController::class, 'notificar']);
});

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
});