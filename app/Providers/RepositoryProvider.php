<?php

namespace App\Providers;

use App\Repositories\CarteiraRepository;
use App\Repositories\Contracts\CarteiraRepositoryInteface;
use App\Repositories\Contracts\PessoaRepositoryInterface;
use App\Repositories\Contracts\TransferenciaRepositoryInterface;
use App\Repositories\PessoaRepository;
use App\Repositories\TransferenciaRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CarteiraRepositoryInteface::class, CarteiraRepository::class);

        $this->app->bind(TransferenciaRepositoryInterface::class, TransferenciaRepository::class);

        $this->app->bind(PessoaRepositoryInterface::class, PessoaRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
