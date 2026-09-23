<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Help Center: let logged-out VISITORS raise a message too, and allow an
 * image attachment on any message.
 *
 *   complaints.user_id         now nullable — a visitor has no account
 *   complaints.source          'student' | 'visitor', drives the admin badge
 *   complaints.guest_name      who the visitor said they were
 *   complaints.guest_email     how to reach them (they have no inbox)
 *   complaints.attachment_url  public path of the uploaded image
 *   complaints.attachment_name original filename, shown to the admin
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (! Schema::hasColumn('complaints', 'source')) {
                $table->string('source')->default('student')->after('user_id');
            }
            if (! Schema::hasColumn('complaints', 'guest_name')) {
                $table->string('guest_name')->nullable()->after('source');
            }
            if (! Schema::hasColumn('complaints', 'guest_email')) {
                $table->string('guest_email')->nullable()->after('guest_name');
            }
            if (! Schema::hasColumn('complaints', 'attachment_url')) {
                $table->string('attachment_url')->nullable()->after('message');
            }
            if (! Schema::hasColumn('complaints', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attachment_url');
            }
        });

        // Existing rows all came from students.
        DB::table('complaints')->whereNull('source')->update(['source' => 'student']);

        // A visitor has no account, so user_id must accept NULL. The original
        // column was foreignId()->constrained()->cascadeOnDelete().
        Schema::table('complaints', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            foreach (['source', 'guest_name', 'guest_email', 'attachment_url', 'attachment_name'] as $column) {
                if (Schema::hasColumn('complaints', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Visitor rows have no user to point at, so clear them before
        // restoring the NOT NULL constraint.
        DB::table('complaints')->whereNull('user_id')->delete();

        Schema::table('complaints', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
