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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->decimal('google_rating', 2, 1)->nullable();
            $table->integer('review_count')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->json('additional_data')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();
            
            $table->index(['campaign_id', 'email']);
            $table->index(['business_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
