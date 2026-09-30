<?php

namespace App\Providers;

use App\Domain\Library\BookRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentBookRepository;
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
        $this->app->bind(BookRepository::class, EloquentBookRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
