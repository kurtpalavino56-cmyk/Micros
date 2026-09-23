<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Certificate builder support.
 *
 *   users.signature_image  drawn signature, stored as a data URL
 *   users.signature_text   typed signature
 *   users.signature_font   font chosen for the typed signature
 *
 *   courses.certificate_mode      'auto' | 'upload'
 *   courses.certificate_file      uploaded certificate, when mode = upload
 *   courses.certificate_signature snapshot of the issuer's signature at the
 *                                 time the certificate was configured
 *
 *   certificates.serial   public, unguessable verification code carried in
 *                         the QR. Kept separate from the row id so the URL
 *                         does not expose how many have been issued.
 *
 * The signature snapshot on the course is deliberate: a faculty member who
 * later changes their signature should not silently rewrite certificates
 * that were already issued under the old one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'signature_image')) {
                $table->longText('signature_image')->nullable()->after('avatar_url');
            }
            if (! Schema::hasColumn('users', 'signature_text')) {
                $table->string('signature_text')->nullable()->after('signature_image');
            }
            if (! Schema::hasColumn('users', 'signature_font')) {
                $table->string('signature_font')->nullable()->after('signature_text');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'certificate_mode')) {
                $table->string('certificate_mode')->default('auto')->after('certificate_title');
            }
            if (! Schema::hasColumn('courses', 'certificate_file')) {
                $table->string('certificate_file')->nullable()->after('certificate_mode');
            }
            if (! Schema::hasColumn('courses', 'certificate_signature')) {
                $table->longText('certificate_signature')->nullable()->after('certificate_file');
            }
            if (! Schema::hasColumn('courses', 'certificate_signature_name')) {
                $table->string('certificate_signature_name')->nullable()->after('certificate_signature');
            }
        });

        Schema::table('certificates', function (Blueprint $table) {
            if (! Schema::hasColumn('certificates', 'serial')) {
                $table->string('serial', 32)->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (Schema::hasColumn('certificates', 'serial')) {
                $table->dropUnique(['serial']);
                $table->dropColumn('serial');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            foreach (['certificate_mode', 'certificate_file', 'certificate_signature', 'certificate_signature_name'] as $c) {
                if (Schema::hasColumn('courses', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['signature_image', 'signature_text', 'signature_font'] as $c) {
                if (Schema::hasColumn('users', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
