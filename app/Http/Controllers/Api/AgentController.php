<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\SupportAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\PromptRequest;
use Illuminate\Support\Str;
use Laravel\Ai\Enums\Lab;
use Throwable;

class AgentController extends Controller
{
    public function ask(PromptRequest $request)
{
    $validated = $request->validated();
    $start = microtime(true);

    $prompt = $validated['prompt'];

    $agents = [
        [
            'name' => 'gemini',
            'provider' => Lab::Gemini,
            'model' => 'gemini-2.5-flash',
        ],
        [
            'name' => 'openai',
            'provider' => Lab::OpenAI,
            'model' => 'gpt-4.1-mini',
        ],
        [
            'name' => 'groq',
            'provider' => Lab::Groq,
            'model' => 'llama-3.3-70b-versatile',
        ],
    ];

    foreach ($agents as $agent) {
        try {
            $response = (new SupportAgent())->prompt(
                $prompt,
                provider: $agent['provider'],
                model: $agent['model']
            );

            $usedProvider = $agent['name'];
            $usedModel = $agent['model'];
            break;

        } catch (Throwable $e) {
            if (!$this->shouldSwitchProvider($e)) {
                throw $e; 
            }
        }
    }

    if (!isset($response)) {
        return response()->json([
            'status' => false,
            'message' => 'All AI providers failed.',
        ], 500);
    }

    $content = array_values(array_filter(
        preg_split("/\r\n|\r|\n/", trim((string) $response))
    ));

    // Store the interaction in the database
    \App\Models\Agent::create([
        'user_id' => auth()->id(),
        'prompt' => $prompt,
        'response' => (string) $response,
        'agent' => $usedProvider,
        'model' => $usedModel,
        'time' => round(microtime(true) - $start, 3) . ' Seconds',
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Agent response generated and stored successfully.',
        'agent' => $usedProvider,
        'model' => $usedModel,
        'time' => round(microtime(true) - $start, 3) . ' Seconds',
        'data' => [
            'content' => $content,
        ],
    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

    private function shouldSwitchProvider(Throwable $e): bool
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