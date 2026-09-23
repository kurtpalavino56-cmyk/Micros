<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * users.announcements_read_at — the moment a faculty member last pressed
 * "Mark all as read" in their inbox.
 *
 * Kept separate from users.notifications_read_at, which is the watermark for
 * the notification bell's live activity feed. Sharing one column would make
 * reading the inbox silently clear the bell as well.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'announcements_read_at')) {
                $table->timestamp('announcements_read_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'announcements_read_at')) {
                $table->dropColumn('announcements_read_at');
            }
        });
    }
};
