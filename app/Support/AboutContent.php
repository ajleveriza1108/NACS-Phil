<?php

namespace App\Support;

class AboutContent
{
    public static function defaults(): array
    {
        return [
            'hero_badge' => 'About NACS-Phil',
            'hero_heading' => 'A school community built on',
            'hero_highlight' => 'truth, care, and purpose.',
            'hero_lead' => 'This page is prepared for the school-approved history, mission, vision, Christian values, leadership message, and official school identity.',

            'story_kicker' => 'Our Story',
            'story_heading' => 'Tell the NACS-Phil story faithfully.',
            'story_body' => 'Add the school-approved history here. Explain why the school was founded, whom it serves, and the milestones that shaped its ministry and educational work.',
            'story_note' => 'Official historical wording should be reviewed and approved by authorized school leadership before public launch.',

            'mission_title' => 'Our Mission',
            'mission_text' => 'Add the official school-approved mission statement here.',
            'vision_title' => 'Our Vision',
            'vision_text' => 'Add the official school-approved vision statement here.',

            'faith_kicker' => 'Biblical Foundation',
            'faith_heading' => 'Christ at the center of learning and life.',
            'faith_text' => 'Add the school-approved statement explaining how biblical truth shapes teaching, character, relationships, service, and the life of the school community.',
            'verse_text' => 'Except the LORD build the house, they labour in vain that build it.',
            'verse_reference' => 'Psalm 127:1 KJV',

            'values_heading' => 'Values that shape the school community.',
            'value_1_title' => 'Faith',
            'value_1_text' => 'Confirm and replace this description with the school-approved wording for faith.',
            'value_2_title' => 'Character',
            'value_2_text' => 'Confirm and replace this description with the school-approved wording for character.',
            'value_3_title' => 'Excellence',
            'value_3_text' => 'Confirm and replace this description with the school-approved wording for excellence.',
            'value_4_title' => 'Service',
            'value_4_text' => 'Confirm and replace this description with the school-approved wording for service.',

            'leadership_kicker' => 'Leadership',
            'leadership_heading' => 'A message from school leadership.',
            'leader_name' => '',
            'leader_role' => 'School Principal / Administrator',
            'leader_message' => 'Add the approved leadership welcome message here. This section can introduce families to the schoolâ€™s heart for learners, teachers, parents, and Christian education.',

            'community_heading' => 'Growing together from the early years through Junior High.',
            'community_text' => 'NACS-Phil serves learners through Preschool, Elementary, and Junior High programs. Program-specific details will be refined during the Programs page phase.',

            'cta_heading' => 'Discover the learning community behind NACS-Phil.',
            'cta_text' => 'Explore the school programs or contact the school for approved information about enrollment and campus life.',
            'cta_programs_button' => 'Explore Programs',
            'cta_contact_button' => 'Contact the School',
        ];
    }
}