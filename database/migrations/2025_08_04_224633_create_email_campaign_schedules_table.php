<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaign_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('template_id')->constrained('email_templates')->onDelete('cascade');
            $table->json('contact_list_ids'); // IDs des listes de contacts ciblées
            $table->json('campaign_ids')->nullable(); // IDs des campagnes ciblées (optionnel)
            $table->timestamp('scheduled_at'); // Date/heure d'envoi programmée
            $table->string('status')->default('scheduled'); // scheduled, sending, sent, failed
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('send_options')->nullable(); // Options d'envoi (délais, etc.)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_schedules');
    }
};