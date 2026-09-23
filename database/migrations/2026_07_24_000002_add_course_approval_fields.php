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
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'approval_status')) {
                $table->string('approval_status')->default('pending')->after('is_published');
            }
            if (! Schema::hasColumn('courses', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('approval_status');
            }
            if (! Schema::hasColumn('courses', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('is_approved')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('courses', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            foreach (['approval_status', 'is_approved', 'approved_at'] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
