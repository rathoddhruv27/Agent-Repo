<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider Names
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the AI providers below should be the
    | default for AI operations when no explicit provider is provided
    | for the operation. This should be any provider defined below.
    |
    */

    'default' => 'anthropic',
    'default_for_images' => 'gemini',
    'default_for_audio' => 'gemini',
    'default_for_transcription'=> 'openai',
    'default_for_embeddings' => 'gemini',
    'default_for_reranking' => 'groq',

    /*
    |--------------------------------------------------------------------------
    | Fallback Provider Chain
    |--------------------------------------------------------------------------
    |
    | The AiProviderService will iterate this chain in order, automatically
    | switching to the next provider when one fails due to:
    |   - Invalid / expired API key (HTTP 401 / 403)
    |   - Rate limit (HTTP 429)
    |   - Quota exhausted / insufficient credits
    |   - Provider overloaded (HTTP 503)
    |   - Any other Throwable
    |
    | Each entry: provider name (must match a key in 'providers' below) => model.
    |
    */

    'fallback_chain' => [
        'anthropic' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20240620'),
        'gemini' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'openai' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
        'groq' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'deepseek' =>env('DEEPSEEK_MODEL', 'deepseek-v3.2-exp'), 
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Below you may configure caching strategies for AI related operations
    | such as embedding generation. You are free to adjust these values
    | based on your application's available caching stores and needs.
    |
    */

    'caching' => [
        'embeddings' => [
            'cache' => false,
            'store' => env('CACHE_STORE', 'database'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Below are each of your AI providers defined for this application. Each
    | represents an AI provider and API key combination which can be used
    | to perform tasks like text, image, and audio creation via agents.
    |
    */

    'providers' => [
        'anthropic' => [
            'driver' => 'anthropic',
            'key' => env('ANTHROPIC_API_KEY'),
            'url' => env('ANTHROPIC_URL', 'https://api.anthropic.com/v1'),
        ],

         'gemini' => [
            'driver' => 'gemini',
            'key'    => env('GEMINI_API_KEY'),
            'model'  => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        ],

        'openai' => [
            'driver' => 'openai',
            'key'    => env('OPENAI_API_KEY'),
            'model'  => env('OPENAI_MODEL', 'gpt-4.1-mini'),
            'url'    => env('OPENAI_URL', 'https://api.openai.com/v1'),
        ],

        'groq' => [
            'driver' => 'groq',
            'key'    => env('GROQ_API_KEY'),
            'url'    => env('GROQ_URL', 'https://api.groq.com/openai/v1'),
        ],

        'deepseek' => [
            'driver' => 'deepseek',
            'key' => env('DEEPSEEK_API_KEY'),
            'url' => env('DEEPSEEK_URL', 'https://api.deepseek.com/v1'),
        ],

        // ── Other providers ───────────────────────────────────────────────

        'anthropic' => [
            'driver' => 'anthropic',
            'key'    => env('ANTHROPIC_API_KEY'),
            'url'    => env('ANTHROPIC_URL', 'https://api.anthropic.com/v1'),
        ],

        'azure' => [
            'driver' => 'azure',
            'key' => env('AZURE_OPENAI_API_KEY'),
            'url' => env('AZURE_OPENAI_URL'),
            'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-10-21'),
            'deployment' => env('AZURE_OPENAI_DEPLOYMENT', 'gpt-4o'),
            'embedding_deployment' => env('AZURE_OPENAI_EMBEDDING_DEPLOYMENT', 'text-embedding-3-small'),
        ],

        'cohere' => [
            'driver' => 'cohere',
            'key' => env('COHERE_API_KEY'),
        ],

        'eleven' => [
            'driver' => 'eleven',
            'key' => env('ELEVENLABS_API_KEY'),
        ],

        'gemini' => [
            'driver' => 'gemini',
            'key' => env('GEMINI_API_KEY'),
        ],

        'groq' => [
            'driver' => 'groq',
            'key' => env('GROQ_API_KEY'),
            'url' => env('GROQ_URL', 'https://api.groq.com/openai/v1'),
        ],

        'jina' => [
            'driver' => 'jina',
            'key' => env('JINA_API_KEY'),
        ],

        'mistral' => [
            'driver' => 'mistral',
            'key' => env('MISTRAL_API_KEY'),
            'url' => env('MISTRAL_URL', 'https://api.mistral.ai/v1'),
        ],

        'ollama' => [
            'driver' => 'ollama',
            'key' => env('OLLAMA_API_KEY', ''),
            'url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        ],

        'openai' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
        ],  

        // 'gemini' => [
        //     'driver' => 'gemini',
        //     'key' => env('GEMINI_API_KEY'),
        // ],

        'openrouter' => [
            'driver' => 'openrouter',
            'key' => env('OPENROUTER_API_KEY'),
        ],

        'voyageai' => [
            'driver' => 'voyageai',
            'key' => env('VOYAGEAI_API_KEY'),
        ],

        'xai' => [
            'driver' => 'xai',
            'key' => env('XAI_API_KEY'),
            'url' => env('XAI_URL', 'https://api.x.ai/v1'),
        ],
    ],

];
