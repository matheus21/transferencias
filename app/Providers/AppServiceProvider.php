<?php

namespace App\Providers;

use App\Domain\Services\CadastrarTransferenciaService;
use App\Domain\Services\Contracts\CadastrarTransferencia;
use App\Domain\Services\Contracts\EfetivarTransferencias;
use App\Domain\Services\EfetivarTransferenciasService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CadastrarTransferencia::class, CadastrarTransferenciaService::class);

        $this->app->bind(EfetivarTransferencias::class, EfetivarTransferenciasService::class);

        $this->app->bind(ClientInterface::class, Client::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
