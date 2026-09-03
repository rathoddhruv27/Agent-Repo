<?php

namespace App\Services;

use App\Ai\Agents\SupportAgent;
use Laravel\Ai\Enums\Lab;
use Throwable;
use Illuminate\Support\Str;

class AiProviderService
{
    /**
     * The fallback chain of providers and models.
     */
    protected array $fallbackChain;

    public function __construct()
    {
        $this->fallbackChain = config('ai.fallback_chain', []);
    }

    /**
     * Execute a prompt using the fallback mechanism.
     * 
     * @param string $prompt
     * @return array
     * @throws Throwable
     */
    public function prompt(string $prompt): array
    {
        $start = microtime(true);
        $response = null;
        $usedProvider = null;
        $usedModel = null;

        foreach ($this->fallbackChain as $providerName => $model) {
            try {
                $providerEnum = $this->mapToLab($providerName);
                
                $response = (new SupportAgent())->prompt(
                    $prompt,
                    provider: $providerEnum,
                    model: $model
                );

                $usedProvider = $providerName;
                $usedModel = $model;
                break;

            } catch (Throwable $e) {
                if (!$this->shouldSwitchProvider($e)) {
                    throw $e;
                }
            }
        }

        if (!$response) {
            throw new \Exception('All AI providers failed to generate a response.');
        }

        return [
            'response' => (string) $response,
            'provider' => $usedProvider,
            'model'    => $usedModel,
            'time'     => round(microtime(true) - $start, 3) . ' Seconds',
        ];
    }

    /**
     * Map provider string name to Lab enum.
     */
    protected function mapToLab(string $provider): Lab
    {
        return match (strtolower($provider)) {
            'anthropic'=> Lab::Anthropic,
            'gemini'   => Lab::Gemini,
            'openai'   => Lab::OpenAI,
            'groq'     => Lab::Groq,
            'deepseek' => Lab::DeepSeek,
            default    => Lab::Gemini,
        };
    }

    /**
     * Determine if we should switch to the next provider based on the error.
     */
    protected function shouldSwitchProvider(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return Str::contains($message, [
            'rate limit',
            'too many requests',
            'overloaded',
            'quota',
            'exceeded',
            'expired',
            'invalid api key',
            'authentication',
            'unauthorized',
            '403',
            '429',
        ]);
    }
}
