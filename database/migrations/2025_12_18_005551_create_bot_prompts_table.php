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
        Schema::create('bot_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'system_prompt', 'greeting', 'collection_flow'
            $table->string('name'); // Human-readable name
            $table->text('description')->nullable(); // What this prompt does
            $table->longText('content'); // The actual prompt text
            $table->string('category')->default('general'); // general, flow, template, etc.
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0); // For sorting
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_prompts');
    }
};
