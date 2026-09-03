<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class AiCredentialService
{
    /**
     * Resolve and apply AI API keys from Database / User Overrides / Environment.
     * Priority: User Personal Key > Database Settings Table Key > .env File Key
     */
    public static function applyCredentials(?User $user = null): void
    {
        $providers = [
            'gemini'    => ['db' => 'gemini_api_key',    'user' => 'gemini_api_key',    'env' => 'GEMINI_API_KEY'],
            'openai'    => ['db' => 'openai_api_key',    'user' => 'openai_api_key',    'env' => 'OPENAI_API_KEY'],
            'groq'      => ['db' => 'groq_api_key',      'user' => 'groq_api_key',      'env' => 'GROQ_API_KEY'],
            'deepseek'  => ['db' => 'deepseek_api_key',  'user' => 'deepseek_api_key',  'env' => 'DEEPSEEK_API_KEY'],
            'anthropic' => ['db' => 'anthropic_api_key', 'user' => 'anthropic_api_key', 'env' => 'ANTHROPIC_API_KEY'],
        ];

        foreach ($providers as $providerName => $keys) {
            $userKey = $user ? $user->{$keys['user']} : null;
            $dbKey   = Setting::get($keys['db']);
            $envKey  = env($keys['env']);

            // Pick the highest priority key available
            $finalKey = !empty($userKey) ? $userKey : (!empty($dbKey) ? $dbKey : $envKey);

            if (!empty($finalKey)) {
                // Set for Laravel AI package config
                Config::set("ai.providers.{$providerName}.key", $finalKey);
                // Set for standard Laravel services config
                Config::set("services.{$providerName}.key", $finalKey);
                // Put back into env dynamically for any SDK reading putenv/env
                putenv("{$keys['env']}={$finalKey}");
                $_ENV[$keys['env']] = $finalKey;
                $_SERVER[$keys['env']] = $finalKey;
            }
        }
    }
}
