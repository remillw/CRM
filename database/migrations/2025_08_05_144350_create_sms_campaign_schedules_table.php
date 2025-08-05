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
        Schema::create('sms_campaign_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('sms_template_id')->constrained()->cascadeOnDelete();
            $table->json('contact_list_ids');
            $table->json('filters')->nullable(); // Pour filtrer par critères (pas d'email, pas de site, etc.)
            $table->datetime('scheduled_at');
            $table->boolean('is_test')->default(false);
            $table->integer('total_recipients')->default(0);
            $table->enum('status', ['scheduled', 'sending', 'sent', 'failed'])->default('scheduled');
            $table->datetime('sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_campaign_schedules');
    }
};
