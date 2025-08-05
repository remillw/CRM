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
        Schema::table('email_sends', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['campaign_id']);
            
            // Modify column to be nullable
            $table->foreignId('campaign_id')->nullable()->change();
            
            // Re-add foreign key constraint
            $table->foreign('campaign_id')->references('id')->on('email_campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_sends', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['campaign_id']);
            
            // Modify column to not be nullable
            $table->foreignId('campaign_id')->nullable(false)->change();
            
            // Re-add foreign key constraint
            $table->foreign('campaign_id')->references('id')->on('email_campaigns')->onDelete('cascade');
        });
    }
};
