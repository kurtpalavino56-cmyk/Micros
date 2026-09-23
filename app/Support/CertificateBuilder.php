<?php

namespace App\Support;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;

/**
 * CertificateBuilder — assembles everything the certificate view needs.
 *
 * One place decides what a certificate says, so the faculty preview, the
 * student's copy and the public verification page cannot drift apart and
 * start showing different titles or dates for the same award.
 */
class CertificateBuilder
{
    /** Fonts offered for a typed signature. Keys are stored in the DB. */
    public const SIGNATURE_FONTS = [
        'Great Vibes' => "'Great Vibes', cursive",
        'Dancing Script' => "'Dancing Script', cursive",
        'Parisienne' => "'Parisienne', cursive",
        'Sacramento' => "'Sacramento', cursive",
        'Homemade Apple' => "'Homemade Apple', cursive",
    ];

    /** CSS font-family for a stored font name, with a safe fallback. */
    public static function fontStack(?string $name): string
    {
        return self::SIGNATURE_FONTS[$name] ?? "'Great Vibes', cursive";
    }

    /**
     * Credential ID printed on the certificate and encoded in the QR.
     *
     * Format: PSU-LC-MC-YYYY-NNNNNN
     *   PSU-LC-MC  fixed prefix (Lingayen Campus, Micro-credential)
     *   YYYY       year of issue
     *   NNNNNN     six random digits, re-rolled on the rare collision
     *
     * Random rather than sequential on purpose: a running counter would
     * tell anyone holding one certificate how many have ever been issued,
     * and would let them guess their neighbours' IDs.
     */
    public static function newSerial(): string
    {
        $year = now()->format('Y');

        do {
            $serial = sprintf('PSU-LC-MC-%s-%06d', $year, random_int(0, 999999));
        } while (Certificate::where('serial', $serial)->exists());

        return $serial;
    }

    /**
     * Public URL a QR code points at.
     *
     * Deliberately the homepage with a query string rather than a separate
     * page: the brief is that scanning lands on UPSKILL with the
     * certificate floating over it, so the visitor sees the real site and
     * not an isolated document.
     */
    public static function verifyUrl(string $serial): string
    {
        return url('/?certificate='.urlencode($serial));
    }

    /** QR image URL for a serial, sized for the given pixel width. */
    public static function qrUrl(string $serial, int $size = 180): string
    {
        return self::qrFor(self::verifyUrl($serial), $size);
    }

    /** QR image for any URL. */
    public static function qrFor(string $url, int $size = 180): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size
             .'&data='.urlencode($url);
    }

    /**
     * The fields the certificate view renders.
     *
     * $certificate may be null when previewing: faculty need to see the
     * layout before anybody has earned one.
     */
    public static function data(Course $course, ?User $student = null, ?Certificate $certificate = null): array
    {
        $issuer = $course->creator;

        return [
            'course_title' => $course->title,
            // Raw, not defaulted: the certificate view falls back to the
            // course name for the credential line, and a hardcoded
            // "Certificate of Completion" here would print in its place.
            'certificate_title' => $course->title,
            'student_name' => $student?->name ?? 'Student Name',
            'issuer_name' => $course->certificate_signature_name
                                ?: ($issuer?->name ?? $course->instructor ?? 'Course Instructor'),
            'issuer_role' => $issuer?->displayRole() ?? 'Faculty',
            // "date were created" in the brief: the course's creation date,
            // not the date the student finished.
            'course_created' => optional($course->created_at)->format('F j, Y'),
            'course_description' => $course->description,
            // "Learning Hours" — like Date Completed, only meaningful once
            // the student has actually finished, so it stays NULL while
            // previewing rather than advertising a figure for an award
            // nobody has earned yet.
            'learning_hours' => $certificate
                                ? self::hoursLabel($course->duration)
                                : null,
            'has_certificate' => (bool) $certificate,
            'issued_at' => optional($certificate?->issued_at)->format('F j, Y') ?? now()->format('F j, Y'),
            // Date Completed — the moment the student finished, which is
            // when the certificate row was written. Left NULL while
            // previewing: printing today's date on an unissued certificate
            // reads as a real completion date and is simply untrue.
            'date_completed' => $certificate?->issued_at
                                ? $certificate->issued_at->format('F j, Y')
                                : null,
            'serial' => $certificate?->serial,
            // Before a student completes the course there is no serial, so
            // the code points at the course page. It is a working QR from
            // the moment the designer opens; each issued certificate then
            // carries its own serial.
            'qr_url' => $certificate?->serial
                                ? self::qrUrl($certificate->serial)
                                : self::qrFor(url('/explore')),
            'verify_url' => $certificate?->serial ? self::verifyUrl($certificate->serial) : null,
            'signature_img' => $course->certificate_signature,
            'signature_font' => self::fontStack($course->creator?->signature_font),
            'signature_text' => $course->creator?->signature_text,
        ];
    }

    /** "12" -> "12 Hours"; "4 weeks" -> "4 weeks"; empty -> a dash. */
    public static function hoursLabel(?string $duration): string
    {
        $d = trim((string) $duration);

        if ($d === '') {
            return '—';
        }

        return is_numeric($d) ? $d.' Hours' : $d;
    }

    // ------------------------------------------------------------
    // Official issued-certificate PDF (Phase 3, Step 5).
    //
    // Deliberately separate from data() above: data() is the faculty
    // PREVIEW, built from the live, mutable Course row (correct for a
    // preview — the course hasn't been completed by anyone yet). Once a
    // Certificate row exists, it is the record of what a specific learner
    // was actually awarded, so everything below reads ONLY the
    // Certificate's own immutable snapshot columns for the fields that
    // have one. Nothing here ever writes to a snapshot column.
    // ------------------------------------------------------------

    /**
     * Assembles the data for an ISSUED certificate — used for PDF
     * rendering and for public verification alike, so both surfaces are
     * guaranteed to agree and neither drifts from the course's current
     * state. Every field that has a Phase 2 snapshot column is read from
     * the Certificate, not from `course`. Fields with no snapshot column
     * (signature image/name, QR/verify URL, which are derived from the
     * stable `serial`) still read the course/creator, matching the
     * existing convention — this is unavoidable since Step 4 did not add
     * a signature snapshot column, and is intentionally left as-is here.
     */
    public static function pdfData(Certificate $certificate): array
    {
        $certificate->loadMissing(['user', 'course.creator']);
        $course = $certificate->course;
        $student = $certificate->user;

        return [
            'institution' => 'Pangasinan State University – Lingayen Campus',
            'certificate_title' => $certificate->microcredential_title_snapshot ?: optional($course)->title,
            'course_title' => $certificate->microcredential_title_snapshot ?: optional($course)->title,
            'student_name' => $student?->name ?? 'Student Name',
            'issuer_name' => optional($course)->certificate_signature_name
                                ?: (optional($course)->creator?->name ?? optional($course)->instructor ?? 'Course Instructor'),
            'issuer_role' => optional($course)->creator?->displayRole() ?? 'Faculty',
            'learning_outcomes' => $certificate->learning_outcomes_snapshot ?? [],
            'competencies' => $certificate->competencies_snapshot ?? [],
            'pqf_level' => $certificate->pqf_level_snapshot,
            'credit_equivalency' => $certificate->credit_equivalency_snapshot,
            'learning_hours' => $certificate->learning_hours_snapshot,
            'issued_at' => optional($certificate->issued_at)->format('F j, Y'),
            'date_completed' => optional($certificate->issued_at)->format('F j, Y'),
            'serial' => $certificate->serial,
            'status' => $certificate->status,
            'qr_url' => self::qrUrl($certificate->serial),
            'verify_url' => self::verifyUrl($certificate->serial),
            'signature_img' => optional($course)->certificate_signature,
            'signature_font' => self::fontStack(optional($course)->creator?->signature_font),
            'signature_text' => optional($course)->creator?->signature_text,
        ];
    }

    /**
     * Renders the issued-certificate PDF as raw bytes. Requires
     * barryvdh/laravel-dompdf (not bundled — see the composer note in the
     * Step 5 report). Reads only pdfData(), i.e. only the certificate's
     * own snapshots plus the few non-snapshot display fields above —
     * never anything else from the live course.
     */
    public static function renderPdf(Certificate $certificate): string
    {
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf', [
            'cert' => self::pdfData($certificate),
        ])->setPaper('a4', 'landscape')->output();
    }

    /**
     * Idempotent: if a generated file already exists on disk for this
     * certificate, does nothing and returns it unchanged. Otherwise
     * renders and stores exactly one PDF, then sets `file_path` — the
     * ONLY field this method ever writes. It never touches
     * microcredential_title_snapshot, learning_outcomes_snapshot,
     * competencies_snapshot, pqf_level_snapshot,
     * credit_equivalency_snapshot, or learning_hours_snapshot; those are
     * read-only inputs here, exactly as captured at issuance in Step 4.
     *
     * Re-evaluating an already-issued certificate (e.g. evaluate() being
     * called again) is therefore safe to call this repeatedly: it will
     * never regenerate or duplicate the file once one exists.
     */
    public static function ensureFileGenerated(Certificate $certificate): Certificate
    {
        $relativePath = 'certificates/'.$certificate->serial.'.pdf';

        if (
            $certificate->file_path
            && \Illuminate\Support\Facades\Storage::disk('public')->exists($relativePath)
        ) {
            return $certificate;
        }

        $pdf = self::renderPdf($certificate);
        \Illuminate\Support\Facades\Storage::disk('public')->put($relativePath, $pdf);

        $certificate->file_path = 'storage/'.$relativePath;
        $certificate->save();

        return $certificate;
    }
}
