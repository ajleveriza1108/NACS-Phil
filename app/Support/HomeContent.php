<?php

namespace App\Support;

class HomeContent
{
    public static function defaults(): array
    {
        return [
            'hero_badge' => 'Christ-Centered Education',
            'hero_heading' => 'Shaping the Future Through',
            'hero_highlight' => 'Faith, Character, and Excellence',
            'hero_lead' => 'NACS-Phil is building a caring learning community where knowledge, Christian character, and a heart to serve grow together.',
            'hero_primary_button' => 'Explore Programs',
            'hero_secondary_button' => 'Start Admission',
            'hero_image_path' => '',
            'hero_image_alt' => 'Concept illustration of a modern NACS-Phil school campus.',
            'hero_image_focus_x' => '50',
            'hero_image_focus_y' => '50',
            'hero_image_zoom' => '1',

            'why_heading' => 'A school experience shaped by truth, care, and purpose.',
            'why_intro' => 'Clean information for parents, meaningful learning for students, and a Christ-centered foundation for the whole school community.',
            'why_1_title' => 'Academic Excellence',
            'why_1_text' => 'Learning experiences designed to develop understanding, discipline, creativity, and strong academic foundations.',
            'why_2_title' => 'Christian Values',
            'why_2_text' => 'Biblical truth should be reflected not only in lessons, but also in character, relationships, service, and daily choices.',
            'why_3_title' => 'Caring Teachers',
            'why_3_text' => 'Teachers serve as educators and mentors who encourage learners to grow in knowledge, confidence, responsibility, and kindness.',
            'why_4_title' => 'Safe Learning Environment',
            'why_4_text' => 'A school community should be orderly, welcoming, privacy-aware, and committed to protecting the dignity and welfare of every child.',

            'programs_heading' => 'Growing with students at every learning stage.',
            'preschool_text' => 'A warm, age-appropriate beginning that nurtures curiosity, language, relationships, movement, creativity, and early character formation.',
            'elementary_text' => 'Building strong foundations in literacy, numeracy, science, creativity, communication, responsibility, and Christian character.',
            'junior_high_text' => 'Preparing learners for wise choices, responsible study, meaningful service, leadership, and future academic opportunities.',

            'updates_heading' => 'Latest announcements and important dates.',
            'updates_intro' => 'Families should be able to find current information quickly without searching through a long social-media feed.',

            'life_heading' => 'A community where students can grow academically, spiritually, and socially.',

            'cta_heading' => 'Discover whether NACS-Phil is the right school for your family.',
            'cta_text' => 'Review the admissions information and begin with only the details the school genuinely needs.',
            'cta_button' => 'Explore Admissions',

            'footer_tagline' => 'Faith. Character. Excellence.',
            'contact_phone' => '',
            'contact_email' => '',
            'contact_address' => '',
            'facebook_url' => '',
        ];
    }

    public static function editableKeys(): array
    {
        return array_keys(static::defaults());
    }
}
