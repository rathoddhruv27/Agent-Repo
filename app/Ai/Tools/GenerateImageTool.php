<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Image;
use Laravel\Ai\Responses\Data\GeneratedImage;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

class GenerateImageTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Generates an image based on a prompt using Gemini image models. Call this whenever the user asks for a picture or image of something.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'prompt' => $schema->string()
                ->description('A highly detailed visual description of the image to generate.')
                ->required(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $prompt = (string) $request['prompt'];
        $result = static::generateImage($prompt);

        return static::renderImageHtml($result['url'], $prompt);
    }

    /**
     * Generate an image trying Gemini first, then OpenAI, then Pollinations as fallback.
     *
     * @return array{url: string, path: ?string, provider: string, model: string}
     */
    public static function generateImage(string $prompt, ?string $preferredProvider = null, array $attachments = []): array
    {
        if ($preferredProvider === 'pollinations') {
            return static::generateWithPollinations($prompt);
        }

        if ($preferredProvider === 'openai') {
            try {
                return static::generateWithOpenAi($prompt, $attachments);
            } catch (Throwable $e) {
                Log::warning('OpenAI image generation failed, falling back to Pollinations: ' . $e->getMessage());

                return static::generateWithPollinations($prompt);
            }
        }

        try {
            return static::generateWithGemini($prompt, null, $attachments);
        } catch (Throwable $e) {
            Log::warning('Gemini image generation failed, trying fallback provider: ' . $e->getMessage());
        }

        if (! empty(config('ai.providers.openai.key'))) {
            try {
                return static::generateWithOpenAi($prompt, $attachments);
            } catch (Throwable $e) {
                Log::warning('OpenAI image fallback failed, falling back to Pollinations: ' . $e->getMessage());
            }
        }

        return static::generateWithPollinations($prompt);
    }

    /**
     * Generate an image using Gemini models and store it on the public disk.
     *
     * @return array{url: string, path: string, provider: string, model: string}
     *
     * @throws Throwable
     */
    public static function generateWithGemini(string $prompt, ?string $model = null, array $attachments = []): array
    {
        $defaultModel = config('ai.providers.gemini.models.image.default', 'gemini-3.1-flash-image-preview');
        $models = array_values(array_unique(array_filter([
            $model,
            $defaultModel,
            'gemini-2.5-flash-image',
        ])));

        $lastException = null;

        foreach ($models as $candidateModel) {
            try {
                $pending = Image::of($prompt)->square();

                if (! empty($attachments)) {
                    $pending->attachments($attachments);
                }

                $response = $pending->generate(Lab::Gemini, $candidateModel);
                $stored = static::storeGeneratedImage($response->firstImage());

                return [
                    'url' => $stored['url'],
                    'path' => $stored['path'],
                    'provider' => 'gemini',
                    'model' => $response->meta->model ?: $candidateModel,
                ];
            } catch (Throwable $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new \RuntimeException('Gemini image generation failed.');
    }

    /**
     * Generate an image using OpenAI and store it on the public disk.
     *
     * @return array{url: string, path: string, provider: string, model: string}
     *
     * @throws Throwable
     */
    public static function generateWithOpenAi(string $prompt, array $attachments = []): array
    {
        $pending = Image::of($prompt)->square();

        if (! empty($attachments)) {
            $pending->attachments($attachments);
        }

        $response = $pending->generate(Lab::OpenAI);
        $stored = static::storeGeneratedImage($response->firstImage());

        return [
            'url' => $stored['url'],
            'path' => $stored['path'],
            'provider' => 'openai',
            'model' => $response->meta->model ?: 'gpt-image-1',
        ];
    }

    /**
     * Generate an image URL via Pollinations AI fallback.
     *
     * @return array{url: string, path: null, provider: string, model: string}
     */
    public static function generateWithPollinations(string $prompt): array
    {
        $encodedPrompt = urlencode($prompt);

        return [
            'url' => "https://image.pollinations.ai/prompt/{$encodedPrompt}?width=1024&height=1024&nologo=true",
            'path' => null,
            'provider' => 'pollinations',
            'model' => 'flux',
        ];
    }

    /**
     * Persist a generated image on the public disk and return its URL and path.
     *
     * @return array{url: string, path: string}
     */
    protected static function storeGeneratedImage(GeneratedImage $generated): array
    {
        $extension = match ($generated->mime) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => 'png',
        };

        $filename = 'generated_images/' . Str::random(40) . '.' . $extension;
        Storage::disk('public')->put($filename, $generated->content());

        return [
            'url' => '/storage/' . $filename,
            'path' => $filename,
        ];
    }

    /**
     * Render the generated image HTML card for the chat UI.
     */
    public static function renderImageHtml(string $imageUrl, string $prompt): string
    {
        $escapedUrl = e($imageUrl);
        $escapedPrompt = e($prompt);

        return <<<HTML
<div class="generated-image-container my-3" style="width: 100%; max-width: 512px;">
    <style>
        @keyframes skeleton-pulse {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
    <div class="image-wrapper" style="position: relative; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2); background: #1a1a1a; aspect-ratio: 1 / 1; width: 100%;">
        <div class="skeleton-loader" style="position: absolute; inset: 0; background: linear-gradient(90deg, #222 25%, #333 50%, #222 75%); background-size: 200% 100%; animation: skeleton-pulse 1.5s infinite linear;"></div>
        <img src="{$escapedUrl}" alt="{$escapedPrompt}" onload="this.previousElementSibling.style.display='none'; this.style.opacity=1; this.nextElementSibling.style.opacity=1;" style="width: 100%; height: 100%; object-fit: cover; display: block; cursor: zoom-in; opacity: 0; transition: opacity 0.5s ease; position: relative; z-index: 1;" onclick="openFullscreenImage(this.src)">
        <div class="image-actions" style="position: absolute; bottom: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: center; opacity: 0; transition: opacity 0.5s ease; z-index: 2;">
            <button type="button" class="btn-image-action" style="background: rgba(255,255,255,0.85); color: #333; border: none; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; backdrop-filter: blur(4px); box-shadow: 0 2px 6px rgba(0,0,0,0.15);" onclick="alert('Image editing coming soon!')">
                Edit
            </button>
            <a href="{$escapedUrl}" download="generated.png" target="_blank" style="background: rgba(255,255,255,0.85); color: #333; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(4px); box-shadow: 0 2px 6px rgba(0,0,0,0.15); text-decoration: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </a>
        </div>
    </div>
</div>
HTML;
    }
}
