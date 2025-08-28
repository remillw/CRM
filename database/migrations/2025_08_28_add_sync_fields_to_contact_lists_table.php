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
        Schema::table('contact_lists', function (Blueprint $table) {
            $table->boolean('auto_sync')->default(false)->after('status');
            $table->integer('sync_campaign_id')->nullable()->after('auto_sync');
            $table->json('sync_criteria')->nullable()->after('sync_campaign_id');
            $table->timestamp('last_synced_at')->nullable()->after('sync_criteria');
            $table->integer('synced_contacts_count')->default(0)->after('last_synced_at');
            
            $table->foreign('sync_campaign_id')->references('id')->on('campaigns')->onDelete('set null');
            $table->index(['auto_sync', 'sync_campaign_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_lists', function (Blueprint $table) {
            $table->dropForeign(['sync_campaign_id']);
            $table->dropColumn(['auto_sync', 'sync_campaign_id', 'sync_criteria', 'last_synced_at', 'synced_contacts_count']);
        });
    }
};