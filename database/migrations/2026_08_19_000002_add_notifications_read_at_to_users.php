<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * users.notifications_read_at — the moment the user last pressed
 * "Mark all as read".
 *
 * The notifications page mixes two sources: rows in the notifications table
 * (which carry their own is_read flag) and a live activity feed built on the
 * fly from courses, enrolments and badges. Feed entries have nowhere to
 * store a read flag, so they were hardcoded unread and never cleared.
 * Comparing each entry's timestamp against this watermark makes them behave
 * like real notifications.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'notifications_read_at')) {
                $table->timestamp('notifications_read_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'notifications_read_at')) {
                $table->dropColumn('notifications_read_at');
            }
        });
    }
};
