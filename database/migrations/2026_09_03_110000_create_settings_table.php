<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('api_credentials');
                $table->timestamps();
            });
        }

        // Seed initial API credentials into settings table if present in environment
        $defaultKeys = [
            'gemini_api_key' => env('GEMINI_API_KEY'),
            'openai_api_key' => env('OPENAI_API_KEY'),
            'groq_api_key' => env('GROQ_API_KEY'),
            'deepseek_api_key' => env('DEEPSEEK_API_KEY'),
            'anthropic_api_key' => env('ANTHROPIC_API_KEY'),
        ];

        foreach ($defaultKeys as $key => $val) {
            if ($val) {
                \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $val, 'group' => 'api_credentials', 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
