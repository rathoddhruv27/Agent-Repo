<?php

namespace App\Ai\Tools;

use Laravel\Ai\Tools\Tool;
use Laravel\Ai\Tools\ToolRequest;

class GenerateImageTool extends Tool
{
    /**
     * Tool name visible to the model.
     */
    protected string $name = 'generate_image';

    /**
     * Description of what this tool does.
     */
    protected string $description = 'Generates an image based on a prompt. Call this whenever the user asks for a picture or image of something (e.g., "Show me an image of...", "Generate a picture of..."). It will return a beautiful image to the user.';

    /**
     * The input required for this tool.
     */
    public function schema(): array
    {
        return [
            'prompt' => [
                'type' => 'string',
                'description' => 'A highly detailed visual description of the image to generate.',
            ],
        ];
    }

    /**
     * Execute the tool.
     */
    public function execute(ToolRequest $request): string
    {
        $prompt = $request->input('prompt');
        $encodedPrompt = urlencode($prompt);
        
        // Use Pollinations AI for free, instant, keyless image generation
        $imageUrl = "https://image.pollinations.ai/prompt/{$encodedPrompt}?width=1024&height=1024&nologo=true";

        // We wrap the image in a special div to style it nicely in the frontend
        return <<<HTML
<div class="generated-image-container my-3">
    <div class="image-wrapper" style="position: relative; display: inline-block; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
        <img src="{$imageUrl}" alt="{$prompt}" style="max-width: 100%; height: auto; display: block; border-radius: 12px; cursor: zoom-in;" onclick="openFullscreenImage(this.src)">
        <div class="image-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 12px; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; font-size: 0.85rem; opacity: 0; transition: opacity 0.2s;">
            Generated Image
        </div>
        <a href="{$imageUrl}" download="generated_image.jpg" target="_blank" class="download-btn" style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.5); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; backdrop-filter: blur(4px); opacity: 0; transition: opacity 0.2s;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
        </a>
    </div>
</div>
HTML;
    }
}
