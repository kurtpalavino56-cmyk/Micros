<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Repairs certificates created while the old implementation stored the
 * generic "Certificate of Completion" as the certificate title/snapshot.
 *
 * The actual microcredential name comes from courses.title. Only rows that
 * still contain the old generic title are changed; intentionally customized
 * legacy values are left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('certificates')
            ->where(function ($query) {
                $query->where('title', 'Certificate of Completion')
                    ->orWhere('microcredential_title_snapshot', 'Certificate of Completion')
                    ->orWhereNull('microcredential_title_snapshot');
            })
            ->orderBy('id')
            ->chunkById(100, function ($certificates): void {
                foreach ($certificates as $certificate) {
                    $courseTitle = DB::table('courses')
                        ->where('id', $certificate->course_id)
                        ->value('title');

                    $courseTitle = trim((string) $courseTitle);

                    if ($courseTitle === '') {
                        continue;
                    }

                    $updates = [];

                    if (($certificate->title ?? null) === 'Certificate of Completion') {
                        $updates['title'] = $courseTitle;
                    }

                    if (($certificate->microcredential_title_snapshot ?? null) === 'Certificate of Completion'
                        || $certificate->microcredential_title_snapshot === null) {
                        $updates['microcredential_title_snapshot'] = $courseTitle;
                    }

                    if ($updates !== []) {
                        DB::table('certificates')
                            ->where('id', $certificate->id)
                            ->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        // The migration intentionally does not restore the generic title.
        // Reverting would reintroduce the defect this migration repairs.
    }
};
