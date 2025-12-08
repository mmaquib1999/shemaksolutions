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
        Schema::create('ai_provider_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider');      // openai, anthropic, xai, deepseek, google
            $table->string('model');         // gpt-4o, claude-3.5, grok-3 etc
            $table->string('name');          // display name
            $table->text('api_key');         // encrypted
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('total_queries')->default(0);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_provider_keys');
    }
};
