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
        Schema::create('administrative_forms', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 50)->index();
            $table->string('form_code', 50)->index();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('description')->nullable();
            $table->string('authority', 100);
            $table->string('download_url')->nullable();
            $table->string('online_url')->nullable();
            $table->json('keywords');
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
        Schema::dropIfExists('administrative_forms');
    }
};
