<?php

namespace App\Providers;

use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Global Bootstrap 5 Pagination
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Blade directives for Indian currency & phone formatting
        Blade::directive('inr', function ($expression) {
            return "<?php echo \App\Helpers\CurrencyHelper::format($expression); ?>";
        });

        Blade::directive('formatInr', function ($expression) {
            return "<?php echo \App\Helpers\CurrencyHelper::formatInr($expression); ?>";
        });

        Blade::directive('phone', function ($expression) {
            return "<?php echo \App\Helpers\CurrencyHelper::formatPhone($expression); ?>";
        });
    }
}
