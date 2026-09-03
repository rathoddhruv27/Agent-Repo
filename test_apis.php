<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$agents = [
    ['name' => 'gemini', 'provider' => Laravel\Ai\Enums\Lab::Gemini, 'model' => 'gemini-1.5-flash'],
    ['name' => 'openai', 'provider' => Laravel\Ai\Enums\Lab::OpenAI, 'model' => 'gpt-4o-mini'],
    ['name' => 'groq', 'provider' => Laravel\Ai\Enums\Lab::Groq, 'model' => 'llama-3.3-70b-versatile'],
    ['name' => 'deepseek', 'provider' => Laravel\Ai\Enums\Lab::DeepSeek, 'model' => 'deepseek-chat'],
];

foreach ($agents as $a) {
    try {
        $agent = new App\Ai\Agents\SupportAgent();
        $response = $agent->prompt("Hello", provider: $a['provider'], model: $a['model']);
        echo "{$a['name']} SUCCESS: " . substr($response, 0, 50) . "\n";
    } catch (Throwable $e) {
        echo "{$a['name']} FAILED: " . $e->getMessage() . "\n";
    }
}
