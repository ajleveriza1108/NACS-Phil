<?php

namespace App\Support;

class AdmissionsContent
{
    public static function defaults(): array
    {
        return [
            'hero_badge' => 'Admissions',
            'hero_heading' => 'A clear, welcoming path to',
            'hero_highlight' => 'joining the NACS-Phil community.',
            'hero_lead' => 'Use this page for school-approved enrollment steps, current requirements, dates, FAQs, and family guidance. Exact policies should be confirmed before public launch.',

            'welcome_heading' => 'Start with the right information.',
            'welcome_text' => 'Admissions should be simple for families to understand. Authorized school staff can update the words and requirements without changing the protected responsive design.',

            'step_1_title' => 'Ask & Explore',
            'step_1_text' => 'Contact the school or review the Programs page to identify the appropriate learning stage.',
            'step_2_title' => 'Prepare Requirements',
            'step_2_text' => 'Gather only the documents and information listed in the school-approved admissions requirements.',
            'step_3_title' => 'Submit & Review',
            'step_3_text' => "Follow the school's approved submission process. If an online application workflow is enabled, it remains handled by the existing admissions system.",
            'step_4_title' => 'Receive Guidance',
            'step_4_text' => 'The school will provide the next approved enrollment steps, schedules, or follow-up instructions.',

            'requirements_heading' => 'Admissions requirements',
            'requirements_intro' => 'Replace the placeholders below with the exact current requirements approved by school leadership.',
            'requirement_1_title' => 'Requirement 1',
            'requirement_1_text' => 'Add the first official admissions requirement here.',
            'requirement_2_title' => 'Requirement 2',
            'requirement_2_text' => 'Add the second official admissions requirement here.',
            'requirement_3_title' => 'Requirement 3',
            'requirement_3_text' => 'Add the third official admissions requirement here.',
            'requirement_4_title' => 'Requirement 4',
            'requirement_4_text' => 'Add the fourth official admissions requirement here.',
            'requirements_note' => 'Requirements may vary by grade level or applicant status. Confirm the final wording and any exceptions before publishing.',

            'dates_heading' => 'Important admissions dates',
            'dates_text' => 'Add the current application, assessment, enrollment, orientation, or opening-of-classes dates here when officially approved. Avoid publishing tentative dates as final.',
            'school_year_label' => 'School Year',
            'school_year_value' => 'Update in Content Manager',
            'status_label' => 'Admissions Status',
            'status_value' => 'Contact the school for current availability',

            'faq_heading' => 'Frequently asked questions',
            'faq_1_q' => 'What grade levels does NACS-Phil offer?',
            'faq_1_a' => 'NACS-Phil currently presents Preschool, Elementary Grades 1-6, and Junior High Grades 7-10 on this website. Confirm current availability with the school.',
            'faq_2_q' => 'How do I know which requirements apply to my child?',
            'faq_2_a' => 'Use the school-approved requirements listed on this page, then contact the school if the applicant has a special circumstance or transfer history.',
            'faq_3_q' => 'Can I ask questions before applying?',
            'faq_3_a' => 'Yes. Families can contact the school for current admissions guidance before beginning the enrollment process.',
            'faq_4_q' => 'Where will online application instructions appear?',
            'faq_4_a' => 'If the school activates an online application workflow, its official access point can be presented alongside this information without changing the underlying admissions system.',

            'privacy_heading' => 'Family information should be handled carefully.',
            'privacy_text' => 'Only request information necessary for the admissions process, use approved channels, and avoid publishing private applicant information on public pages.',

            'cta_heading' => 'Ready to ask about enrollment?',
            'cta_text' => 'Contact the school for current availability, official requirements, and the next approved step for your child.',
            'cta_primary_button' => 'Contact Admissions',
            'cta_secondary_button' => 'Explore Programs',
        ];
    }

    /**
     * Normalize legacy punctuation corruption in admissions copy without changing stored data.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function normalize(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $values[$key] = self::normalizeText($value);
            }
        }

        return $values;
    }

    private static function normalizeText(string $value): string
    {
        $replacements = [
            // Correct UTF-8 typographic punctuation -> plain ASCII.
            'e28098' => "'",
            'e28099' => "'",
            'e2809c' => '"',
            'e2809d' => '"',
            'e28093' => '-',
            'e28094' => '-',
            'c2a0' => ' ',

            // Common UTF-8/Windows-1252 mojibake already seen in legacy admissions copy.
            'c3a2e282accb9c' => "'",
            'c3a2e282ace284a2' => "'",
            'c3a2e282acc593' => '"',
            'c3a2e282acc29d' => '"',
            'c3a2e282ace2809c' => '-',
            'c3a2e282ace2809d' => '-',
            'c382c2a0' => ' ',
        ];

        uksort(
            $replacements,
            static fn (string $left, string $right): int => strlen($right) <=> strlen($left)
        );
        foreach ($replacements as $hex => $replacement) {
            $needle = hex2bin($hex);

            if (is_string($needle)) {
                $value = str_replace($needle, $replacement, $value);
            }
        }

        return $value;
    }
}
