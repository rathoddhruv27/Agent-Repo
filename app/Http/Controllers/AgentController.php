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
    /**
     * Get all models organized by category for the dropdown UI.
     */
    private function getCategorizedModels(): array
    {
        return [
            [
                'category' => 'Chat & Q&A',
                'icon' => '💬',
                'color' => '#3b82f6',
                'models' => [
                    ['name' => 'anthropic', 'provider' => Lab::Anthropic, 'model' => 'claude-3-5-sonnet-20240620', 'label' => 'Claude 3.5 Sonnet', 'desc' => 'Anthropic\'s most intelligent model', 'vision' => true],
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-2.5-flash', 'label' => 'Gemini 2.5 Flash', 'desc' => 'Google\'s fast multimodal model', 'vision' => true],
                    ['name' => 'openai', 'provider' => Lab::OpenAI, 'model' => 'gpt-4o-mini', 'label' => 'GPT-4o Mini', 'desc' => 'OpenAI\'s efficient flagship', 'vision' => true],
                    ['name' => 'groq', 'provider' => Lab::Groq, 'model' => 'qwen/qwen3.6-27b', 'label' => 'Qwen 3.6 27B', 'desc' => 'Ultra-fast via Groq inference', 'vision' => false],
                    ['name' => 'deepseek', 'provider' => Lab::DeepSeek, 'model' => 'deepseek-chat', 'label' => 'DeepSeek Chat', 'desc' => 'Cost-effective reasoning model', 'vision' => false],
                ],
            ],
            [
                'category' => 'Coding & Development',
                'icon' => '💻',
                'color' => '#10b981',
                'models' => [
                    ['name' => 'anthropic', 'provider' => Lab::Anthropic, 'model' => 'claude-3-5-sonnet-20240620', 'label' => 'Claude Code', 'desc' => 'Best-in-class code generation', 'vision' => true],
                    ['name' => 'deepseek', 'provider' => Lab::DeepSeek, 'model' => 'deepseek-coder', 'label' => 'DeepSeek Coder', 'desc' => 'Specialized coding model', 'vision' => false],
                    ['name' => 'groq', 'provider' => Lab::Groq, 'model' => 'llama-3.3-70b-versatile', 'label' => 'Llama 3.3 70B', 'desc' => 'Meta\'s open-source powerhouse', 'vision' => false],
                ],
            ],
            [
                'category' => 'Image Generation',
                'icon' => '🎨',
                'color' => '#8b5cf6',
                'models' => [
                    ['name' => 'openai', 'provider' => Lab::OpenAI, 'model' => 'dall-e-3', 'label' => 'DALL·E 3', 'desc' => 'OpenAI image generation', 'vision' => false, 'type' => 'image'],
                    ['name' => 'pollinations', 'provider' => null, 'model' => 'flux', 'label' => 'Flux (Pollinations)', 'desc' => 'Free high-quality image gen', 'vision' => false, 'type' => 'image'],
                    ['name' => 'pollinations', 'provider' => null, 'model' => 'stable-diffusion', 'label' => 'Stable Diffusion XL', 'desc' => 'Open-source image generation', 'vision' => false, 'type' => 'image'],
                ],
            ],
            [
                'category' => 'Reasoning & Analysis',
                'icon' => '🧠',
                'color' => '#06b6d4',
                'models' => [
                    ['name' => 'openai', 'provider' => Lab::OpenAI, 'model' => 'o4-mini', 'label' => 'OpenAI o4-mini', 'desc' => 'Advanced reasoning model', 'vision' => false],
                    ['name' => 'deepseek', 'provider' => Lab::DeepSeek, 'model' => 'deepseek-reasoner', 'label' => 'DeepSeek R1', 'desc' => 'Chain-of-thought reasoning', 'vision' => false],
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-2.5-flash-thinking', 'label' => 'Gemini Thinking', 'desc' => 'Google\'s reasoning model', 'vision' => true],
                ],
            ],
            [
                'category' => 'Research & Web Search',
                'icon' => '🔍',
                'color' => '#eab308',
                'models' => [
                    ['name' => 'groq', 'provider' => Lab::Groq, 'model' => 'llama-3.3-70b-versatile', 'label' => 'Llama Search', 'desc' => 'Fast web-augmented search', 'vision' => false],
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-2.5-flash', 'label' => 'Gemini Search', 'desc' => 'Google-powered search AI', 'vision' => true],
                ],
            ],
            [
                'category' => 'Multimodal',
                'icon' => '🌐',
                'color' => '#14b8a6',
                'models' => [
                    ['name' => 'openai', 'provider' => Lab::OpenAI, 'model' => 'gpt-4o', 'label' => 'GPT-4o', 'desc' => 'OpenAI\'s best multimodal model', 'vision' => true],
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-2.5-pro', 'label' => 'Gemini 2.5 Pro', 'desc' => 'Google\'s most capable model', 'vision' => true],
                    ['name' => 'anthropic', 'provider' => Lab::Anthropic, 'model' => 'claude-3-5-sonnet-20240620', 'label' => 'Claude 3.5 Vision', 'desc' => 'Anthropic multimodal', 'vision' => true],
                ],
            ],
            [
                'category' => 'Audio & Speech',
                'icon' => '🎵',
                'color' => '#f97316',
                'models' => [
                    ['name' => 'openai', 'provider' => Lab::OpenAI, 'model' => 'gpt-4o-mini', 'label' => 'Whisper (via GPT)', 'desc' => 'Speech-to-text transcription', 'vision' => false],
                ],
            ],
            [
                'category' => 'Video Generation',
                'icon' => '🎬',
                'color' => '#ef4444',
                'models' => [
                    ['name' => 'pollinations', 'provider' => null, 'model' => 'sora-placeholder', 'label' => 'Sora (Coming Soon)', 'desc' => 'OpenAI video generation', 'vision' => false, 'type' => 'video', 'coming_soon' => true],
                ],
            ],
            [
                'category' => 'Data Analysis',
                'icon' => '📊',
                'color' => '#ec4899',
                'models' => [
                    ['name' => 'openai', 'provider' => Lab::OpenAI, 'model' => 'gpt-4o-mini', 'label' => 'Code Interpreter', 'desc' => 'Data analysis & visualization', 'vision' => false],
                    ['name' => 'deepseek', 'provider' => Lab::DeepSeek, 'model' => 'deepseek-chat', 'label' => 'DeepSeek Analyst', 'desc' => 'Structured data reasoning', 'vision' => false],
                ],
            ],
        ];
    }

    /**
     * Get all chat-capable agents as a flat array for the fallback engine.
     */
    private function getAvailableAgents(): array
    {
        return [
            ['name' => 'anthropic', 'provider' => Lab::Anthropic, 'model' => 'claude-3-5-sonnet-20240620'],
            ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-2.5-flash'],
            ['name' => 'openai', 'provider' => Lab::OpenAI, 'model' => 'gpt-4o-mini'],
            ['name' => 'groq', 'provider' => Lab::Groq, 'model' => 'qwen/qwen3.6-27b'],
            ['name' => 'deepseek', 'provider' => Lab::DeepSeek, 'model' => 'deepseek-chat'],
        ];
    }

    public function agent(Request $request, $id = null) {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'profile_image')) {
            \Illuminate\Support\Facades\Schema::table('users', function ($table) {
                $table->string('profile_image')->nullable()->after('email');
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'custom_instructions_about')) {
            \Illuminate\Support\Facades\Schema::table('users', function ($table) {
                $table->text('custom_instructions_about')->nullable();
                $table->text('custom_instructions_respond')->nullable();
                $table->boolean('custom_instructions_enabled')->default(true);
            });
        }

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
        
        $availableModels = $this->getAvailableAgents();
        $categorizedModels = $this->getCategorizedModels();

        return view('agent', compact('messages', 'agents', 'currentConversationId', 'availableModels', 'categorizedModels'));
    }

    public function ask(PromptRequest $request)
    {
        $validated = $request->validated();
        $start = microtime(true);

        $prompt = $validated['prompt'];

        $response = null;
        $usedProvider = null;
        $usedModel = null;
        
        $imagePath = null;
        $attachments = [];
        
        if ($request->has('image.base64')) {
            $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('image.base64'));
            $mime = $request->input('image.mime', 'image/jpeg');
            $extension = explode('/', $mime)[1] ?? 'jpg';
            
            $filename = 'chat_images/' . Str::random(40) . '.' . $extension;
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, base64_decode($base64Data));
            
            $imagePath = $filename;
            $attachments[] = new \Laravel\Ai\Files\LocalImage(storage_path('app/public/' . $imagePath), $mime);
        } elseif ($request->has('image.url')) {
            $url = $request->input('image.url');
            try {
                $imageResponse = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
                if ($imageResponse->successful()) {
                    $mime = $imageResponse->header('Content-Type') ?: 'image/jpeg';
                    $extension = explode('/', $mime)[1] ?? 'jpg';
                    $filename = 'chat_images/' . Str::random(40) . '.' . $extension;
                    
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $imageResponse->body());
                    $imagePath = $filename;
                    $attachments[] = new \Laravel\Ai\Files\LocalImage(storage_path('app/public/' . $imagePath), $mime);
                }
            } catch (\Exception $e) {
                // Ignore download errors and just proceed without image
            }
        }
        $availableAgents = $this->getAvailableAgents();
        
        if ($request->has('model_selection') && $request->input('model_selection') !== 'auto') {
            $selectedName = $request->input('model_selection');
            $filtered = array_filter($availableAgents, fn($a) => $a['name'] === $selectedName);
            if (!empty($filtered)) {
                $availableAgents = array_values($filtered);
            }
        }
        
        if ($imagePath) {
            $availableAgents = array_values(array_filter($availableAgents, fn($a) => in_array($a['name'], ['anthropic', 'gemini', 'openai'])));
            if (empty($availableAgents)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No vision-capable AI models are configured.',
                ], 400);
            }
        }

        $usedProvider = null;
        $usedModel = null;
        $response = null;

        // --- IMAGE GENERATION INTERCEPTION ---
        // If the user explicitly asks for an image, bypass the LLM and generate it directly!
        if (preg_match('/(generate|create|make|draw|show).*(image|picture|photo|drawing|portrait|logo)/i', $prompt)) {
            // Bypass Tool instantiation since it's hardcoded below
            
            // For Laravel AI ToolRequest constructor, we need to pass data
            // We can just call it directly by bypassing the ToolRequest object or creating one
            
            // Since ToolRequest constructor in laravel/ai might be strict, we can just execute the logic:
            $encodedPrompt = urlencode($prompt);
            $imageUrl = "https://image.pollinations.ai/prompt/{$encodedPrompt}?width=1024&height=1024&nologo=true";
            
            $response = <<<HTML
<div class="generated-image-container my-3" style="width: 100%; max-width: 512px;">
    <style>
        @keyframes skeleton-pulse {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
    <div class="image-wrapper" style="position: relative; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2); background: #1a1a1a; aspect-ratio: 1 / 1; width: 100%;">
        <div class="skeleton-loader" style="position: absolute; inset: 0; background: linear-gradient(90deg, #222 25%, #333 50%, #222 75%); background-size: 200% 100%; animation: skeleton-pulse 1.5s infinite linear;"></div>
        <img src="{$imageUrl}" alt="{$prompt}" onload="this.previousElementSibling.style.display='none'; this.style.opacity=1; this.nextElementSibling.style.opacity=1;" style="width: 100%; height: 100%; object-fit: cover; display: block; cursor: zoom-in; opacity: 0; transition: opacity 0.5s ease; position: relative; z-index: 1;" onclick="openFullscreenImage(this.src)">
        <div class="image-actions" style="position: absolute; bottom: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: center; opacity: 0; transition: opacity 0.5s ease; z-index: 2;">
            <button type="button" class="btn-image-action" style="background: rgba(255,255,255,0.85); color: #333; border: none; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; backdrop-filter: blur(4px); box-shadow: 0 2px 6px rgba(0,0,0,0.15);" onclick="alert('Image editing coming soon!')">
                Edit
            </button>
            <a href="{$imageUrl}" download="generated.jpg" target="_blank" style="background: rgba(255,255,255,0.85); color: #333; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(4px); box-shadow: 0 2px 6px rgba(0,0,0,0.15); text-decoration: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </a>
        </div>
    </div>
</div>
HTML;
            $usedProvider = 'openai';
            $usedModel = 'dall-e-3';
        }

        if (!$response) {
            shuffle($availableAgents);

        for ($i = 0; $i < count($availableAgents); $i++) {
            $agentName = $availableAgents[$i]['name'];
            $provider = $availableAgents[$i]['provider'];
            $model = $availableAgents[$i]['model'];

            try {
                $agent = new SupportAgent();
                
                $request->conversation_id 
                    ? $agent->continue($request->conversation_id, as: auth()->user())
                    : $agent->forUser(auth()->user());

                $response = $agent->prompt(
                    $prompt,
                    attachments: $attachments,
                    provider: $provider,
                    model: $model
                );

                $usedProvider = $agentName;
                $usedModel = $model;
                break;

            } catch (Throwable $e) {
                \Log::warning("AI Provider {$agentName} failed: " . $e->getMessage());
                $lastError = $e;
                
                if (!$this->shouldSwitchProvider($e)) {
                    break;
                }
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

        $content = (string) $response;
        // Strip <think>...</think> internal reasoning blocks some models use
        $content = preg_replace('/<think>.*?<\/think>\s*/is', '', $content);

        $interaction = Agent::create([
            'user_id'         => Auth::id(),
            'conversation_id' => $request->input('conversation_id') ?: (string) Str::uuid(),
            'prompt'          => $prompt,
            'response'        => trim($content),
            'agent'           => $usedProvider,
            'model'           => $usedModel,
            'time'            => round(microtime(true) - $start, 3) . ' Seconds',
            'image_path'      => $imagePath,
        ]);

        if ($promptUuid = $request->input('prompt_uuid')) {
            if (\Illuminate\Support\Facades\Cache::has('stop_prompt_' . $promptUuid)) {
                $interaction->delete();
                return response()->json([
                    'status' => false,
                    'message' => 'Generation stopped by user.',
                ]);
            }
            // Store the interaction ID just in case the stop request arrives a moment later
            \Illuminate\Support\Facades\Cache::put('prompt_interaction_' . $promptUuid, $interaction->id, now()->addMinutes(5));
        }

        return response()->json([
            'status'          => true,
            'message'         => 'Agent response generated and stored successfully.',
            'id'              => $interaction->id,
            'conversation_id' => $interaction->conversation_id,
            'agent'           => $usedProvider,
            'model'           => $usedModel,
            'time'            => round(microtime(true) - $start, 3) . ' Seconds',
            'data'    => [
                'content' => trim($content),
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
            'incorrect api key',
            'does not exist',
            'decommissioned',
            'authentication',
            'unauthorized',
            'insufficient',
            'limit',
            '400',
            '401',
            '403',
            '404',
            '413',
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
