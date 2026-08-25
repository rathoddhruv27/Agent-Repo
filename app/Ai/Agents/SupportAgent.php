<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetLaravelTipsTool;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Attributes\Timeout;

class SupportAgent implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function instructions(): string  // instructions for AI
    {
        $now = now()->toDateTimeString();
        $timezone = config('app.timezone');
        $baseInstructions = <<<TEXT
Rules:
- Answer the user's question with a professional, structured tone.
- Use **Markdown** (headings, bold text, lists, and code blocks) to help structure the information.
- Use clean, well-commented code blocks with appropriate language tags (php, html, css, js, etc).
- Explain step by step.
- Remember previous conversation and context.
- Consume less time for relevant response.
- Keep continuity with previous chat messages in the same conversation.
- Return structured and formatted responses for maximum readability.

Current Date and Time: {$now} ({$timezone})
TEXT;

        $user = auth()->user();
        if ($user && $user->custom_instructions_enabled) {
            $about = trim($user->custom_instructions_about);
            $respond = trim($user->custom_instructions_respond);

            if ($about || $respond) {
                $baseInstructions .= "\n\nUser Custom Instructions (Follow these strictly):";
                if ($about) {
                    $baseInstructions .= "\n- User profile & context (What Aureon should know about the user): " . $about;
                }
                if ($respond) {
                    $baseInstructions .= "\n- How Aureon should respond: " . $respond;
                }
            }
        }

        return $baseInstructions;
    }

    public function tools(): iterable  // php functions to call AI
    // IF tool is relevant to question call tool ELSE generate answer normally
    {
        return [
            new \App\Ai\Tools\GetLaravelTipsTool(),
            new \App\Ai\Tools\SearchWebTool(),
        ];
    }

    // #[Timeout(10)]
    // public function timeout(): int
    // {
    //     return 10;
    // }

}