<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$models = ['gemini-pro', 'gemini-1.5-flash-latest', 'gemini-1.5-pro-latest'];

foreach ($models as $model) {
    try {
        $agent = new App\Ai\Agents\SupportAgent();
        $response = $agent->prompt("Hello", provider: Laravel\Ai\Enums\Lab::Gemini, model: $model);
        echo "{$model} SUCCESS: " . substr($response, 0, 50) . "\n";
    } catch (Throwable $e) {
        echo "{$model} FAILED: " . $e->getMessage() . "\n";
    }
}
