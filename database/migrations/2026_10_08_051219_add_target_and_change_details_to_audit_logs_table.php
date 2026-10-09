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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('target_type', 80)->nullable()->after('event');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            $table->string('target_label', 255)->nullable()->after('target_id');
            $table->json('changes')->nullable()->after('target_label');
            $table->index(['actor_role', 'event', 'created_at'], 'audit_logs_role_event_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_role_event_created_idx');
            $table->dropColumn(['target_type', 'target_id', 'target_label', 'changes']);
        });
    }
};
