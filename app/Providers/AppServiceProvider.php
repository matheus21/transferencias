<?php

namespace App\Providers;

use App\Domain\Services\CadastrarTransferenciaService;
use App\Domain\Services\Contracts\CadastrarTransferencia;
use App\Domain\Services\Contracts\EfetivarTransferencias;
use App\Domain\Services\Contracts\EstornarTransferencia;
use App\Domain\Services\Contracts\NotificarTransferencias;
use App\Domain\Services\EfetivarTransferenciasService;
use App\Domain\Services\EstornarTransferenciaService;
use App\Domain\Services\NotificarTransferenciasService;
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

        $this->app->bind(EstornarTransferencia::class, EstornarTransferenciaService::class);

        $this->app->bind(NotificarTransferencias::class, NotificarTransferenciasService::class);

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
