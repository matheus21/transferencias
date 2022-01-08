<?php

namespace App\Providers;

use App\Domain\Services\CadastrarTransferenciaService;
use App\Domain\Services\Contracts\CadastrarTransferencia;
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
