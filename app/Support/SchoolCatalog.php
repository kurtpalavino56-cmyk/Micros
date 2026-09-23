<?php

namespace App\Support;

/**
 * SchoolCatalog — the list of institutions offered in the "last school,
 * college, or university you attended" picker.
 *
 * Kept here rather than hardcoded in the Blade for the same reason as
 * SkillCatalog: three screens read it (the About Me completion step, the
 * Student Profile edit form, and the Admin user detail page), and a value
 * typed on one screen is compared as a string on the others. Add or remove
 * an institution here and every picker follows.
 *
 * OTHER is deliberately the last entry. Selecting it reveals a free-text
 * box, and what the learner types is stored in users.school in place of the
 * literal word "Other" — so the column always holds a real institution name
 * and nothing downstream has to special-case it.
 */
class SchoolCatalog
{
    public const OTHER = 'Other';

    /** @var list<string> */
    public const SCHOOLS = [
        'Pangasinan State University (PSU)',
        'Urdaneta City University (UCU)',
        'University of Eastern Pangasinan (UEP)',
        'Lyceum Northwestern University (LNU)',
        'Panpacific University',
        'University of Luzon (UL)',
        'Virgen Milagrosa University Foundation (VMUF)',
        'University of Pangasinan (PHINMA UPang)',
        'Universidad de Dagupan',
        'Northern Luzon Adventist College (NLAC)',
        'Philippine College of Science and Technology (PhilCST)',
        'WCC Aeronautical and Technological College',
        'Colegio de San Juan de Letran – Manaoag',
        'San Carlos College',
        'Great Plebeian College',
        'Golden West Colleges',
    ];

    /** Every institution, in display order. */
    public static function all(): array
    {
        return self::SCHOOLS;
    }

    /** Is this value one of the listed institutions? */
    public static function has(?string $value): bool
    {
        return $value !== null && in_array($value, self::SCHOOLS, true);
    }

    /**
     * Which option the picker should preselect for a stored value.
     *
     * A school that is not in the list came from the "Other" box, so the
     * dropdown shows OTHER and the text field is prefilled with the value.
     */
    public static function selectedOption(?string $stored): string
    {
        if (empty($stored)) {
            return '';
        }

        return self::has($stored) ? $stored : self::OTHER;
    }

    /** The text to put in the "please specify" box for a stored value. */
    public static function otherText(?string $stored): string
    {
        if (empty($stored) || self::has($stored)) {
            return '';
        }

        return $stored;
    }
}
