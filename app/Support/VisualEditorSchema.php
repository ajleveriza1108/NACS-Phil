<?php

namespace App\Support;

final class VisualEditorSchema
{
    public static function home(): array
    {
        return [
            'hero_badge' => self::text('Small label', 'Hero / First Screen', 35, 80, 1),
            'hero_heading' => self::text('Main heading', 'Hero / First Screen', 45, 100, 2),
            'hero_highlight' => self::text('Highlighted words', 'Hero / First Screen', 45, 100, 2),
            'hero_lead' => self::area('Short introduction', 'Hero / First Screen', 150, 360, 4),
            'hero_primary_button' => self::text('Programs button', 'Hero / First Screen', 18, 40, 1),
            'hero_secondary_button' => self::text('Admissions button', 'Hero / First Screen', 18, 40, 1),
            'hero_image_alt' => self::text('Photo accessibility description', 'Hero / First Screen', 110, 250, 3),

            'why_heading' => self::text('Section heading', 'Why Choose NACS-Phil', 70, 160, 3),
            'why_intro' => self::area('Section introduction', 'Why Choose NACS-Phil', 180, 420, 5),
            'why_1_title' => self::text('Card 1 title', 'Why Choose NACS-Phil', 36, 80, 2),
            'why_1_text' => self::area('Card 1 description', 'Why Choose NACS-Phil', 160, 360, 5),
            'why_2_title' => self::text('Card 2 title', 'Why Choose NACS-Phil', 36, 80, 2),
            'why_2_text' => self::area('Card 2 description', 'Why Choose NACS-Phil', 160, 360, 5),
            'why_3_title' => self::text('Card 3 title', 'Why Choose NACS-Phil', 36, 80, 2),
            'why_3_text' => self::area('Card 3 description', 'Why Choose NACS-Phil', 160, 360, 5),
            'why_4_title' => self::text('Card 4 title', 'Why Choose NACS-Phil', 36, 80, 2),
            'why_4_text' => self::area('Card 4 description', 'Why Choose NACS-Phil', 160, 360, 5),

            'programs_heading' => self::text('Programs heading', 'Programs Preview', 70, 160, 3),
            'preschool_text' => self::area('Preschool summary', 'Programs Preview', 150, 360, 5),
            'elementary_text' => self::area('Elementary summary', 'Programs Preview', 150, 360, 5),
            'junior_high_text' => self::area('Junior High summary', 'Programs Preview', 150, 360, 5),

            'updates_heading' => self::text('Updates heading', 'Updates / Student Life', 70, 160, 3),
            'updates_intro' => self::area('Updates introduction', 'Updates / Student Life', 170, 420, 5),
            'life_heading' => self::text('Student life heading', 'Updates / Student Life', 80, 180, 3),

            'cta_heading' => self::text('Admissions CTA heading', 'Admissions CTA', 85, 180, 3),
            'cta_text' => self::area('Admissions message', 'Admissions CTA', 160, 420, 5),
            'cta_button' => self::text('Admissions button', 'Admissions CTA', 18, 40, 1),

            'footer_tagline' => self::text('Footer tagline', 'Contact / Footer', 50, 100, 2),
            'contact_phone' => self::optional('Phone', 'Contact / Footer', 'text', 30, 80),
            'contact_email' => self::optional('Email', 'Contact / Footer', 'email', 40, 150),
            'contact_address' => self::optionalArea('School address', 'Contact / Footer', 120, 300, 4),
            'facebook_url' => self::optional('Facebook URL', 'Contact / Footer', 'url', 80, 500),
        ];
    }

    public static function validationRules(): array
    {
        $rules = [];

        foreach (self::home() as $key => $field) {
            $fieldRules = [$field['required'] ? 'required' : 'nullable'];

            if ($field['type'] === 'email') {
                $fieldRules[] = 'email:rfc';
            } elseif ($field['type'] === 'url') {
                $fieldRules[] = 'url:http,https';
            } else {
                $fieldRules[] = 'string';
            }

            $fieldRules[] = 'max:'.$field['max'];
            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    public static function homeImage(): array
    {
        return [
            'label' => 'Homepage hero photograph',
            'frame' => '16:10 landscape',
            'min_width' => 1200,
            'min_height' => 750,
            'max_kb' => 5120,
            'formats' => 'JPG, PNG, or WebP',
        ];
    }

    private static function text(string $label, string $group, int $recommended, int $max, int $lines): array
    {
        return compact('label', 'group', 'recommended', 'max', 'lines') + [
            'type' => 'text',
            'required' => true,
        ];
    }

    private static function area(string $label, string $group, int $recommended, int $max, int $lines): array
    {
        return compact('label', 'group', 'recommended', 'max', 'lines') + [
            'type' => 'textarea',
            'required' => true,
        ];
    }

    private static function optional(string $label, string $group, string $type, int $recommended, int $max): array
    {
        return compact('label', 'group', 'type', 'recommended', 'max') + [
            'lines' => 1,
            'required' => false,
        ];
    }

    private static function optionalArea(string $label, string $group, int $recommended, int $max, int $lines): array
    {
        return compact('label', 'group', 'recommended', 'max', 'lines') + [
            'type' => 'textarea',
            'required' => false,
        ];
    }
}
