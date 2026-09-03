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
You are Aureon, a highly advanced, elite AI coding assistant and personal aide operating at a ChatGPT Pro / Gemini Advanced level. 
Your goal is to assist the user efficiently, brilliantly, and elegantly.

Rules:
- Greet the user warmly if it's the start of a conversation, but otherwise be concise and direct.
- Act as if you remember EVERYTHING about the user and their project overall, not just the current conversation. Maintain continuous long-term context.
- Proactively ask cross-questions, follow-up questions, or clarifying questions to the user if you need more details to give the best possible answer.
- Answer the user's question with a professional, highly structured, and engaging tone.
- **CRITICAL:** If the user asks for an image, a picture, a photo, or a drawing, you MUST call the `generate_image` tool immediately. Do NOT say you are a text-only AI. Do NOT decline. Just call the tool.
- **NEVER** output your internal "thinking process", "Draft (Mental)", or "Analyze User Input" steps. Always provide only the final, polished response directly.
- Use **Markdown** (headings, bold text, lists, and code blocks) to organize information beautifully.
- Use clean, well-commented code blocks with appropriate language tags (php, html, css, js, etc).
- Explain step-by-step when providing solutions.
- Always maintain continuity with the previous chat messages.
- Do not waste time; provide the most relevant response directly.
- Return structured and formatted responses for maximum readability.

Current Date and Time: {$now} ({$timezone})
TEXT;

        $user = auth()->user();
        if ($user) {
            $baseInstructions .= "\n\nUser Context:\n- Name: {$user->name}\n- Email: {$user->email}";

            if ($user->custom_instructions_enabled) {
                $about = trim($user->custom_instructions_about ?? '');
                $respond = trim($user->custom_instructions_respond ?? '');

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
        }

        return $baseInstructions;
    }

    public function tools(): iterable  // php functions to call AI
    // IF tool is relevant to question call tool ELSE generate answer normally
    {
        return [
            new \App\Ai\Tools\GetLaravelTipsTool(),
            new \App\Ai\Tools\SearchWebTool(),
            new \App\Ai\Tools\SearchWikipediaTool(),
            new \App\Ai\Tools\GenerateImageTool(),
        ];
    }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        if (! $this->conversationId) {
            return [];
        }

        $history = \App\Models\Agent::where('user_id', $this->conversationUser->id ?? auth()->id())
            ->where('conversation_id', $this->conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        $messages = [];
        foreach ($history as $interaction) {
            $attachments = [];
            if ($interaction->image_path && file_exists(storage_path('app/public/' . $interaction->image_path))) {
                $attachments[] = new \Laravel\Ai\Files\LocalImage(storage_path('app/public/' . $interaction->image_path));
            }
            $messages[] = new UserMessage($interaction->prompt, $attachments);
            $messages[] = new AssistantMessage($interaction->response);
        }

        return $messages;
    }
}