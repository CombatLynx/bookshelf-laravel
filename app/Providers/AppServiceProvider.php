<?php

namespace App\Providers;

use App\Domain\Library\BookRepository;
use App\Domain\Library\Clock;
use App\Infrastructure\Persistence\Eloquent\EloquentBookRepository;
use App\Infrastructure\Time\SystemClock;
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
        $this->app->bind(Clock::class, SystemClock::class);
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
