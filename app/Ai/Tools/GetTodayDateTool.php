<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetTodayDateTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Use this tool when the user asks for today date or current day.';
    }

    public function handle(Request $request): Stringable|string
    {
        return now()->toDateTimeString();
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}