<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SupportAgent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PromptRequest;
use Illuminate\Support\Str;
use Laravel\Ai\Enums\Lab;
use Throwable;
use Illuminate\Support\Facades\Auth;
use App\Models\Agent;

class AgentController extends Controller
{
    public function agent(Request $request) {
        $lastInteraction = Agent::where('user_id', Auth::id())->latest()->first();
        return view('agent', compact('lastInteraction'));
    }

    public function ask(PromptRequest $request)
    {
        $validated = $request->validated();
        $start = microtime(true);

        $prompt = $validated['prompt'];

        $agents = $request->input('agent', 'gemini', 'openai', 'groq');
        $response = null;
        // $usedProvider = null;
        // $usedModel = null;  

        try {
            switch ($agents) {
                case 'openai':
                    $usedProvider = 'openai';
                    $usedModel = 'gpt-4.1-mini';
                    $response = (new SupportAgent())
                    ->prompt($prompt, provider: Lab::OpenAI, model: $usedModel);
                    break;
                case 'gemini':
                    $usedProvider = 'gemini';
                    $usedModel = 'gemini-2.5-flash';
                    $response = (new SupportAgent())
                    ->prompt($prompt, provider: Lab::Gemini, model: $usedModel);
                    break;
                case 'groq':
                default:
                    $usedProvider = 'groq';
                    $usedModel = 'llama-3.3-70b-versatile';
                    $response = (new SupportAgent())
                    ->prompt($prompt, provider: Lab::Groq, model: $usedModel);
                    break;
            }
        } catch (Throwable $e) {
            report($e);
        }
        dd($e);  
        if (!$response) {
            return response()->json([
                'status' => false,
                'message' => 'All AI providers failed.',
            ], 500);
        }

        Agent::create([
            'user_id' => Auth::id(),
            'prompt' => $prompt,
            'response' => (string) $response,
            'agent' => $usedProvider,
            'model' => $usedModel,
            'time' => round(microtime(true) - $start, 3) . ' Seconds',
        ]);

        $content = array_values(array_filter(
            preg_split("/\r\n|\r|\n/", trim((string) $response))
        ));

        return response()->json([
            'status' => true,
            'message' => 'Agent response generated and stored successfully.',
            'agent' => $usedProvider,
            'model' => $usedModel,
            'time' => round(microtime(true) - $start, 3) . ' Seconds',
            'data' => [
                'content' => $content,
            ],
        ]);
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

    public function history(Request $request)
    {
        $user = Auth::user();
        $agents = Agent::where('user_id', Auth::id())->latest()->get();
        return view('history', compact('agents', 'user'));
    }
}
