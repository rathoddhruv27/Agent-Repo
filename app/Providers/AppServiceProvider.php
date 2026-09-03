<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Ai\Enums\Lab;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Laravel\Ai\Contracts\ConversationStore::class, 
            \App\Ai\Stores\AgentConversationStore::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Apply AI API Credentials from database / environment dynamically
        try {
            \App\Services\AiCredentialService::applyCredentials();
        } catch (\Throwable $e) {
            // Ignore database connection issues during console/migration setup
        }

        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(CarbonInterval::days(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));
    }
}