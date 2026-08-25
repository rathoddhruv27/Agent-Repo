<?php

namespace App\Ai\Tools;

use Laravel\Ai\Tools\Tool;
use Laravel\Ai\Tools\ToolRequest;
use Illuminate\Support\Facades\Http;

class SearchWebTool extends Tool
{
    /**
     * Tool name visible to the model.
     */
    public string $name = 'search_web';

    /**
     * Tell the model when to use this tool.
     */
    public function description(): string
    {
        return 'Use this tool to search the internet for the most up-to-date information, news, release dates, documentation, or facts.';
    }

    /**
     * Input schema for the tool.
     */
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'The search query to look up on the web.',
                ],
            ],
            'required' => ['query'],
        ];
    }

    /**
     * Execute the tool logic.
     */
    public function handle(ToolRequest $request): string
    {
        $query = $request->input('query');

        try {
            $response = Http::asForm()
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
                ->post('https://lite.duckduckgo.com/lite/', [
                    'q' => $query,
                ]);

            if ($response->successful()) {
                $html = $response->body();
                
                // Clean the HTML to extract the text
                $text = strip_tags($html);
                $text = html_entity_decode($text);
                $text = preg_replace('/\s+/', ' ', $text);
                
                // The main content usually starts after "Web Results" or similar.
                // We return a generous substring so the LLM gets the snippets.
                $length = min(strlen($text), 5000);
                return "Web Search Results for '{$query}':\n" . substr($text, 0, $length);
            }

            return "Web search failed. The search engine might be blocking the request. Status: " . $response->status();
        } catch (\Throwable $e) {
            return "Web search encountered an error: " . $e->getMessage();
        }
    }
}
