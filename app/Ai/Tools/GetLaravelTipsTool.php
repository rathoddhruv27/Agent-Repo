<?php

namespace App\Ai\Tools;

use Laravel\Ai\Tools\Tool;
use Laravel\Ai\Tools\ToolRequest;

class GetLaravelTipsTool extends Tool
{
    /**
     * Tool name visible to the model.
     */
    public string $name = 'get_laravel_tips';

    /**
     * Tell the model when to use this tool.
     */
    public function description(): string
    {
        return 'Use this tool when the user asks for Laravel best practices, project structure, or coding guidance.';
    }

    /**
     * Input schema for the tool.
     */
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'topic' => [
                    'type' => 'string',
                    'description' => 'Laravel topic like auth, validation, routes, controllers, middleware, or eloquent.',
                ],
            ],
            'required' => ['topic'],
        ];
    }

    /**
     * Execute the tool logic.
     */
    public function handle(ToolRequest $request): string
    {
        $topic = strtolower($request->input('topic'));

        return match ($topic) {
            'auth' => 'Use Form Requests for validation, middleware for protection, hashed passwords, and API tokens through the Laravel auth stack.',
            'validation' => 'Keep validation inside Form Request classes so controllers remain clean and reusable.',
            'routes' => 'Group related routes with prefixes, middleware, and route names for better structure.',
            'controllers' => 'Keep controllers thin; move business logic to service classes or actions.',
            'eloquent' => 'Use eager loading, validation, guarded/fillable protection, and query scopes for reusable logic.',
            default => 'Use Laravel conventions: Form Requests, resource controllers, service classes, and clear route organization.',
        };
    }
}