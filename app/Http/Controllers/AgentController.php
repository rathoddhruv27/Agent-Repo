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
    public function agent(Request $request, $id = null) {
        $agents = Agent::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->unique(fn($item) => $item->conversation_id ?? $item->id);

        $messages = collect();
        $currentConversationId = null;
        
        if ($id) {
            $interaction = Agent::where('user_id', Auth::id())->where('id', $id)->first();
            if ($interaction) {
                if ($interaction->conversation_id) {
                    $messages = Agent::where('user_id', Auth::id())
                        ->where('conversation_id', $interaction->conversation_id)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    $currentConversationId = $interaction->conversation_id;
                } else {
                    $messages->push($interaction);
                }
            }
        }
        
        return view('agent', compact('messages', 'agents', 'currentConversationId'));
    }

    public function ask(PromptRequest $request)
    {
        $validated = $request->validated();
        $start = microtime(true);

        $prompt = $validated['prompt'];

        $response = null;
        $usedProvider = null;
        $usedModel = null;

        for ($agents = 0; $agents < 4; $agents++) {
            switch ($agents) {
                case 0:
                    $agentName = 'gemini';
                    $provider = Lab::Gemini;
                    $model = 'gemini-3-pro-preview';
                    break;
                case 1:
                    $agentName = 'openai';
                    $provider = Lab::OpenAI;
                    $model = 'gpt-5-nano';
                    break;
                case 2:
                    $agentName = 'groq';
                    $provider = Lab::Groq;
                    $model = 'llama-3.3-70b-versatile';
                    break;
                case 3:
                    $agentName = 'deepseek';
                    $provider = Lab::DeepSeek;
                    $model = 'deepseek-v3.2-exp';
                    break;
                default:
                    $agentName = 'gemini';
                    $provider = Lab::Gemini;
                    $model = 'gemini-3-pro-preview';
                    break;
            }

            try {
                $agent = new SupportAgent();
                
                $request->conversation_id 
                    ? $agent->continue($request->conversation_id, as: auth()->user())
                    : $agent->forUser(auth()->user());

                $response = $agent->prompt(
                    $prompt,
                    provider: $provider,
                    model: $model
                );

                $usedProvider = $agentName;
                $usedModel = $model;
                break;

            } catch (Throwable $e) {
                \Log::warning("AI Provider {$agentName} failed: " . $e->getMessage());
                if ($agents === 3 && !$response) {
                    $lastError = $e;
                }
                if (!$this->shouldSwitchProvider($e) && $agents === 3) {
                    throw $e;
                }
            }
        }

        // dd($e);
        if (!$response) {
            $errorMessage = isset($lastError) ? $lastError->getMessage() : 'All AI providers failed to respond.';
            return response()->json([
                'status' => false,
                'message' => 'AI Fallback Failed: ' . $errorMessage,
            ], 500);
        }

        $interaction = Agent::create([
            'user_id'         => Auth::id(),
            'conversation_id' => $request->input('conversation_id') ?: (string) Str::uuid(),
            'prompt'          => $prompt,
            'response'        => (string) $response,
            'agent'           => $usedProvider,
            'model'           => $usedModel,
            'time'            => round(microtime(true) - $start, 3) . ' Seconds',
        ]);

        $content = array_values(array_filter(
            preg_split("/\r\n|\r|\n/", trim((string) $response))
        ));

        return response()->json([
            'status'          => true,
            'message'         => 'Agent response generated and stored successfully.',
            'id'              => $interaction->id,
            'conversation_id' => $interaction->conversation_id,
            'agent'           => $usedProvider,
            'model'           => $usedModel,
            'time'            => round(microtime(true) - $start, 3) . ' Seconds',
            'data'    => [
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
            '500',
            '502',
            '503',
            '504',
            'timeout',
            'connection',
            'unavailable',
            'not found',
            'empty response',
        ]);
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $agents = Agent::where('user_id', Auth::id())->latest()->get();
        return view('history', compact('agents', 'user'));
    }

    public function prompts(Request $request)
    {
        $user = Auth::user();
        $agents = Agent::where('user_id', Auth::id())->latest()->get();
        return view('prompts', compact('agents', 'user'));
    }

    public function renameHistory(Request $request, $id)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $agent = Agent::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        
        $agent->update(['prompt' => $request->title]);
        
        return redirect()->back();
    }

    public function deleteHistory($id)
    {
        $agent = Agent::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        
        if ($agent->conversation_id) {
            Agent::where('user_id', Auth::id())
                ->where('conversation_id', $agent->conversation_id)
                ->delete();
        } else {
            $agent->delete();
        }
        
        return redirect()->to('/');
    }
}
