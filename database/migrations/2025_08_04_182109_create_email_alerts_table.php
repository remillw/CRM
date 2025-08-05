<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('template_type'); // 'follow_up_1', 'follow_up_2', 'opened_no_response', etc.
            $table->integer('delay_days'); // Nombre de jours après l'email initial
            $table->json('conditions'); // Conditions pour déclencher l'alerte
            $table->foreignId('template_id')->constrained('email_templates')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_alerts');
    }
};