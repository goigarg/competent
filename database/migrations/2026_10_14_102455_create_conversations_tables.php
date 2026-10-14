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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('title')->nullable();
            $table->string('locale', 10)->default('de');
            $table->string('active_workflow')->nullable();
            $table->boolean('is_escalated')->default(false)->index();
            $table->timestamp('escalated_at')->nullable();
            $table->string('escalation_reason')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->longText('case_dossier')->nullable();
            $table->text('officer_notes')->nullable();
            $table->string('assigned_officer')->nullable();
            $table->json('workflow_data')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('uuid', 64)->nullable()->index();
            $table->string('role', 20);
            $table->longText('content');
            $table->json('sources')->nullable();
            $table->boolean('is_fallback')->default(false);
            $table->string('workflow')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
    }
};
