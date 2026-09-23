<?php

namespace App\Support;

/**
 * SkillCatalog — the single skill vocabulary used across UPSKILL.
 *
 * Two screens read from here and MUST stay in sync, because matching is
 * done by string comparison:
 *
 *   · Faculty  → Create/Edit Course → "Subject is Related on"
 *   · Student  → dashboard About Me → "Skills You already Have / want to learn"
 *
 * If a skill exists on one screen but not the other, a course tagged with
 * it can never be recommended to anyone. Add or remove skills here and both
 * pickers, their search boxes and the "Search N skills…" placeholders all
 * follow automatically.
 *
 * A few skills legitimately belong to more than one field (Statistics in
 * both Economics and Mathematics, Therapy in both Nutrition and Social
 * Work, and so on). They are listed under each, and all() de-duplicates.
 */
class SkillCatalog
{
    /**
     * Skills grouped by field of study. Groups appear as headings in the
     * pickers and are searchable alongside the skill names — typing
     * "biology" surfaces that whole group.
     *
     * @var array<string, list<string>>
     */
    public const GROUPS = [
        'Language & Communication' => [
            'Linguistics', 'Syntax', 'Semantics', 'Literature', 'Grammar', 'Communication',
            'Writing', 'Editing', 'Speech', 'Journalism',
        ],
        'Economics' => [
            'Microeconomics', 'Macroeconomics', 'Markets', 'Taxation', 'Inflation',
            'Statistics', 'Trade', 'Finance', 'Policy', 'Wealth',
        ],
        'Computer Science' => [
            'Programming', 'Algorithms', 'Software', 'Coding', 'Artificial Intelligence',
            'Data Structures', 'Computation', 'Logic', 'Mathematics', 'Systems',
        ],
        'Information Technology' => [
            'Networking', 'Cybersecurity', 'Hardware', 'Databases', 'Servers',
            'Troubleshooting', 'Infrastructure', 'Tech Support', 'Web Development',
            'Cloud Computing',
        ],
        'Mathematics' => [
            'Calculus', 'Algebra', 'Statistics', 'Probability', 'Equations', 'Logic',
            'Proofs', 'Modeling', 'Geometry', 'Quantitative Analysis',
        ],
        'Biology' => [
            'Genetics', 'Anatomy', 'Ecosystems', 'Cells', 'Zoology', 'Botany',
            'Microbiology', 'Evolution', 'Physiology', 'Laboratory',
        ],
        'Public Administration' => [
            'Government', 'Policy', 'Law', 'Leadership', 'Bureaucracy', 'Civic Duty',
            'Public Funds', 'Ethics', 'Governance', 'Community',
        ],
        'Operations Management' => [
            'Logistics', 'Supply Chain', 'Efficiency', 'Manufacturing', 'Quality Control',
            'Project Management', 'Inventory', 'Production', 'Strategy', 'Resources',
        ],
        'Finance' => [
            'Banking', 'Investments', 'Capital', 'Corporate Finance', 'Risk Management',
            'Stocks', 'Accounting', 'Assets', 'Wealth Building', 'Budgeting',
        ],
        'Secondary Education' => [
            'Teaching', 'Pedagogy', 'High School', 'Curriculum', 'Lesson Plans',
            'Classroom Management', 'Psychology', 'Grading', 'Instruction',
            'Student Development',
        ],
        'Technology & Livelihood Education' => [
            'Home Economics', 'Basic Trades', 'Junior High', 'Practical Skills',
            'Culinary Arts', 'Drafting', 'Handicrafts', 'Agriculture', 'Livelihood',
            'Self-Sufficiency',
        ],
        'Technical-Vocational' => [
            'Vocational Training', 'Trade Schools', 'Senior High', 'Welding',
            'Automotive Servicing', 'Electrical Work', 'Heavy Machinery', 'Certifications',
            'Technical Instruction', 'Industrial Arts',
        ],
        'Industrial Technology' => [
            'Manufacturing', 'Electronics', 'Automotive', 'Assembly', 'Maintenance',
            'Factory Work', 'Mechanics', 'Machine Operation', 'Engineering Support',
            'Fabrication',
        ],
        'Hospitality & Tourism' => [
            'Hotels', 'Tourism', 'Restaurants', 'Event Planning', 'Customer Service',
            'Culinary', 'Resorts', 'Travel', 'Accommodations', 'Catering',
        ],
        'Nutrition & Dietetics' => [
            'Food Science', 'Diets', 'Health', 'Metabolism', 'Meal Planning',
            'Clinical Nutrition', 'Vitamins', 'Public Health', 'Digestion', 'Therapy',
        ],
        'Social Work' => [
            'Welfare', 'Mental Health', 'Counseling', 'Advocacy', 'Intervention',
            'Community Support', 'Human Rights', 'Therapy', 'Rehabilitation',
            'Crisis Management',
        ],
    ];

    /**
     * Every skill, flattened and de-duplicated, in catalog order.
     * Skills that appear under several groups are returned once.
     */
    public static function all(): array
    {
        static $flat = null;

        if ($flat === null) {
            $flat = [];
            foreach (self::GROUPS as $skills) {
                foreach ($skills as $skill) {
                    $flat[$skill] = true;
                }
            }
            $flat = array_keys($flat);
        }

        return $flat;
    }

    /** Groups, for pickers that show headings. */
    public static function groups(): array
    {
        return self::GROUPS;
    }

    /** Number of distinct skills in the catalog. */
    public static function count(): int
    {
        return count(self::all());
    }
}
