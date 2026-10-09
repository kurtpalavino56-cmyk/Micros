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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('entity_type', 50)->nullable()->after('type');
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
            $table->index(['user_id', 'is_read', 'created_at'], 'notifications_user_read_created_index');
            $table->index(['entity_type', 'entity_id'], 'notifications_entity_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_read_created_index');
            $table->dropIndex('notifications_entity_index');
            $table->dropColumn(['entity_type', 'entity_id']);
        });
    }
};
