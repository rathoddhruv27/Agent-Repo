<?php

namespace App\Ai\Tools;

use Laravel\Ai\Tools\Tool;
use Laravel\Ai\Tools\ToolRequest;
use Illuminate\Support\Facades\Http;

class SearchWikipediaTool extends Tool
{
    /**
     * Tool name visible to the model.
     */
    public string $name = 'search_wikipedia';

    /**
     * Tell the model when to use this tool.
     */
    public function description(): string
    {
        return 'Use this tool to search Wikipedia for encyclopedic facts, historical information, and general knowledge about topics, people, and places.';
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
                    'description' => 'The search query or topic to look up on Wikipedia.',
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
        
        $url = 'https://en.wikipedia.org/w/api.php';
        
        try {
            $response = Http::timeout(10)->get($url, [
                'action' => 'query',
                'format' => 'json',
                'prop' => 'extracts',
                'exchars' => 4000,
                'explaintext' => 1,
                'generator' => 'search',
                'gsrsearch' => $query,
                'gsrlimit' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['query']['pages'])) {
                    // Get the first page returned
                    $page = reset($data['query']['pages']);
                    
                    if (isset($page['extract'])) {
                        $title = $page['title'] ?? $query;
                        $extract = $page['extract'];
                        return "Wikipedia snippet for '{$title}':\n" . $extract;
                    }
                }
                
                return "No relevant Wikipedia article found for '{$query}'.";
            }

            return "Wikipedia search failed. Status: " . $response->status();
        } catch (\Throwable $e) {
            return "Wikipedia search encountered an error: " . $e->getMessage();
        }
    }
}
