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
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-3-flash-preview', 'label' => 'Gemini 3 Flash', 'desc' => 'Google\'s fast multimodal model', 'vision' => true],
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
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-3.1-flash-image-preview', 'label' => 'Gemini 3.1 Flash Image', 'desc' => 'Google Gemini image generation', 'vision' => false, 'type' => 'image'],
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-2.5-flash-image', 'label' => 'Gemini 2.5 Flash Image', 'desc' => 'Google Gemini fast image gen', 'vision' => false, 'type' => 'image'],
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
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-3.1-pro-preview', 'label' => 'Gemini Thinking', 'desc' => 'Google\'s smartest reasoning model', 'vision' => true],
                ],
            ],
            [
                'category' => 'Research & Web Search',
                'icon' => '🔍',
                'color' => '#eab308',
                'models' => [
                    ['name' => 'groq', 'provider' => Lab::Groq, 'model' => 'llama-3.3-70b-versatile', 'label' => 'Llama Search', 'desc' => 'Fast web-augmented search', 'vision' => false],
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-3-flash-preview', 'label' => 'Gemini Search', 'desc' => 'Google-powered search AI', 'vision' => true],
                ],
            ],
            [
                'category' => 'Multimodal',
                'icon' => '🌐',
                'color' => '#14b8a6',
                'models' => [
                    ['name' => 'openai', 'provider' => Lab::OpenAI, 'model' => 'gpt-4o', 'label' => 'GPT-4o', 'desc' => 'OpenAI\'s best multimodal model', 'vision' => true],
                    ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-3.1-pro-preview', 'label' => 'Gemini 3.1 Pro', 'desc' => 'Google\'s most capable model', 'vision' => true],
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
            ['name' => 'gemini', 'provider' => Lab::Gemini, 'model' => 'gemini-3-flash-preview'],
            ['name' => 'openai', 'provider' => Lab::OpenAI, 'model' => 'gpt-4o-mini'],
            ['name' => 'groq', 'provider' => Lab::Groq, 'model' => 'qwen/qwen3.6-27b'],
            ['name' => 'deepseek', 'provider' => Lab::DeepSeek, 'model' => 'deepseek-chat'],
        ];
    }

    public function agent(Request $request, $id = null) {
        try {
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
        } catch (\Throwable $e) {
            \Log::warning("Schema check warning: " . $e->getMessage());
        }

        $agents = Agent::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->unique(fn($item) => $item->conversation_id ?? $item->id);

        $messages = collect();
        $currentConversationId = null;
        
        if ($id) {
            $interaction = Agent::where('user_id', Auth::id())
                ->where(function($q) use ($id) {
                    if (is_numeric($id)) {
                        $q->where('id', $id)->orWhere('conversation_id', (string)$id);
                    } else {
                        $q->where('conversation_id', $id);
                    }
                })
                ->first();

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
            } else {
                return redirect()->to('/');
            }
        }
        
        $availableModels = $this->getAvailableAgents();
        $categorizedModels = $this->getCategorizedModels();

        return view('agent', compact('messages', 'agents', 'currentConversationId', 'availableModels', 'categorizedModels'));
    }

    public function ask(PromptRequest $request)
    {
        // Apply database-stored AI API credentials & user overrides
        \App\Services\AiCredentialService::applyCredentials(auth()->user());

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
        // If the user explicitly asks for an image, try Gemini first with automatic fallback.
        if (preg_match('/(generate|create|make|draw|show).*(image|picture|photo|drawing|portrait|logo)/i', $prompt)) {
            $selectedProvider = $request->input('model_selection', 'auto');
            $result = \App\Ai\Tools\GenerateImageTool::generateImage($prompt, $selectedProvider, $attachments);

            $response = \App\Ai\Tools\GenerateImageTool::renderImageHtml($result['url'], $prompt);
            $usedProvider = $result['provider'];
            $usedModel = $result['model'];
        }

        if (!$response) {
            shuffle($availableAgents);

            for ($i = 0; $i < count($availableAgents); $i++) {
                $agentName = $availableAgents[$i]['name'];
                $provider = $availableAgents[$i]['provider'];
                $model = $availableAgents[$i]['model'];

                // Check if API key exists for this provider before attempting request
                $apiKey = config("ai.providers.{$agentName}.key") ?: config("services.{$agentName}.key");
                if (empty($apiKey)) {
                    \Log::info("Skipping AI Provider {$agentName}: No API key configured.");
                    continue;
                }

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
                }
            }
        }

        if (!$response) {
            return response()->json([
                'status' => false,
                'message' => 'No valid AI API Key configured. Please go to Settings ⚙️ -> API Credentials to add your OpenAI, Gemini, Groq, DeepSeek, or Anthropic API Key.',
            ], 400);
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
        // Always attempt fallback to the next available provider on any exception
        // (missing API keys, 403 unregistered callers, rate limits, 404s, timeouts, etc.)
        return true;
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

        Agent::where('user_id', Auth::id())
            ->where(function($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('conversation_id', (string)$id);
                } else {
                    $q->where('conversation_id', $id);
                }
            })
            ->update(['prompt' => $request->title]);
        
        return redirect()->back();
    }

    public function deleteHistory($id)
    {
        Agent::where('user_id', Auth::id())
            ->where(function($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('conversation_id', (string)$id);
                } else {
                    $q->where('conversation_id', $id);
                }
            })
            ->delete();
        
        return redirect()->to('/');
    }
}
