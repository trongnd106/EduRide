<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        Schema::defaultstringLength(191);

        $minDuration = env('QUERY_LOG_MIN_DURATION', 'none');

        if ($minDuration !== 'none') {
            DB::listen(function ($query) use ($minDuration) {
                if ($query->time >= (double) $minDuration) {
                    $time = microtime(true);
                    Log::debug("SQLQuery - time: " . $time . " duration(ms): " . $query->time . " query: " . $query->sql,
                        ['bindings' => $query->bindings]);
                }
            });
        }
        User::observe(UserObserver::class);
    }
}
