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
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key', 100)->not_null()->primary();
            $table->mediumText('value', 100)->not_null();
            $table->bigInteger('expiration')->not_null()->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key', 100)->not_null()->primary();
            $table->string('owner', 100)->not_null();
            $table->bigInteger('expiration')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
