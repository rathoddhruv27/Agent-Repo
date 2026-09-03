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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'gemini_api_key')) {
                $table->text('gemini_api_key')->nullable();
            }
            if (!Schema::hasColumn('users', 'openai_api_key')) {
                $table->text('openai_api_key')->nullable();
            }
            if (!Schema::hasColumn('users', 'groq_api_key')) {
                $table->text('groq_api_key')->nullable();
            }
            if (!Schema::hasColumn('users', 'deepseek_api_key')) {
                $table->text('deepseek_api_key')->nullable();
            }
            if (!Schema::hasColumn('users', 'anthropic_api_key')) {
                $table->text('anthropic_api_key')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gemini_api_key',
                'openai_api_key',
                'groq_api_key',
                'deepseek_api_key',
                'anthropic_api_key',
            ]);
        });
    }
};
