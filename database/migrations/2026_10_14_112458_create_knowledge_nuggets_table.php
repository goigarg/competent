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
        Schema::create('knowledge_nuggets', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 50)->index();
            $table->string('topic', 100)->index();
            $table->string('title');
            $table->json('keywords');
            $table->text('factual_context');
            $table->string('official_source_url')->nullable();
            $table->boolean('is_verified')->default(true)->index();
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_nuggets');
    }
};
