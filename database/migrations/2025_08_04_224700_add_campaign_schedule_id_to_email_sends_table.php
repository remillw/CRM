<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_sends', function (Blueprint $table) {
            $table->foreignId('campaign_schedule_id')->nullable()->after('campaign_id')->constrained('email_campaign_schedules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('email_sends', function (Blueprint $table) {
            $table->dropForeign(['campaign_schedule_id']);
            $table->dropColumn('campaign_schedule_id');
        });
    }
};