<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_trigger_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('quick_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quick_trigger_category_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('emoji', 16);
            $table->string('action');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['quick_trigger_category_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_triggers');
        Schema::dropIfExists('quick_trigger_categories');
    }
};
