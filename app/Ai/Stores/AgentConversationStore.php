<?php

namespace App\Ai\Stores;

use App\Models\Agent;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

class AgentConversationStore implements ConversationStore
{
    /**
     * Get the most recent conversation ID for a given user.
     */
    public function latestConversationId(string|int $userId): ?string
    {
        return Agent::where('user_id', $userId)
            ->whereNotNull('conversation_id')
            ->latest()
            ->value('conversation_id');
    }

    /**
     * Store a new conversation and return its ID.
     */
    public function storeConversation(string|int|null $userId, string $title): string
    {
        return (string) Str::uuid();
    }

    /**
     * Store a new user message for the given conversation.
     */
    public function storeUserMessage(string $conversationId, string|int|null $userId, AgentPrompt $prompt): string
    {
        return (string) Str::uuid();
    }

    /**
     * Store a new assistant message for the given conversation.
     */
    public function storeAssistantMessage(string $conversationId, string|int|null $userId, AgentPrompt $prompt, AgentResponse $response): string
    {
        // We handle saving manually in AgentController to ensure metadata and timing are preserved.
        return (string) Str::uuid();
    }

    /**
     * Get the latest messages for the given conversation.
     */
    public function getLatestConversationMessages(string $conversationId, int $limit): Collection
    {
        return Agent::where('conversation_id', $conversationId)
            ->oldest()
            ->latest() // Actually, we want the most RECENT up to a limit
            ->take(1000000)
            ->get()
            ->reverse() // Then back to chronological order
            ->flatMap(function ($item) {
                return [
                    new UserMessage($item->prompt),
                    new AssistantMessage($item->response),
                ];
            });
    }
}
