<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

final class AppServiceProvider extends ServiceProvider
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
        RateLimiter::for(
            'admin-login',
            fn (Request $request) => Limit::perMinute(5)->by($this->adminLoginKey($request)),
        );
    }

    private function adminLoginKey(Request $request): string
    {
        $email = $request->input('email');
        $normalizedEmail = is_string($email) ? Str::lower(trim($email)) : 'invalid-email';

        return hash('sha256', $normalizedEmail.'|'.$request->ip());
    }
}
