<?php

namespace App\Providers;

use App\Repositories\Contracts\MonthlyReportRepositoryInterface;
use App\Repositories\Contracts\ResidentRepositoryInterface;
use App\Repositories\EloquentMonthlyReportRepository;
use App\Repositories\EloquentResidentRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ResidentRepositoryInterface::class, EloquentResidentRepository::class);
        $this->app->bind(MonthlyReportRepositoryInterface::class, EloquentMonthlyReportRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
