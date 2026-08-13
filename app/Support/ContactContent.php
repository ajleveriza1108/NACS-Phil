<?php

namespace App\Support;

class ContactContent
{
    public static function defaults(): array
    {
        return [
            'hero_badge' => 'Contact NACS-Phil',
            'hero_heading' => 'A clear path to the',
            'hero_highlight' => 'school office.',
            'hero_lead' => 'Send a general question or admissions inquiry through the secure school form. Messages submitted here are stored for authorized staff to review.',
            'office_heading' => 'School information',
            'office_text' => 'Use the verified contact details below when available. The school can update this page without changing the website design.',
            'address' => (string) config('nacs.address', 'Sariaya, Quezon'),
            'phone' => (string) (config('nacs.phone') ?? ''),
            'email' => (string) (config('nacs.email') ?? ''),
            'facebook_url' => (string) (config('nacs.facebook_url') ?? ''),
            'office_hours' => '',
            'inquiry_heading' => 'How may the school assist you?',
            'inquiry_text' => 'For general questions and admissions interest. Please do not submit medical records, identification documents, grades, or other sensitive student files through this form.',
            'privacy_heading' => 'Your message stays in the school inquiry system.',
            'privacy_text' => 'The form records the information needed for an authorized staff member to respond. Sensitive student documents should be shared only through an approved school process.',
        ];
    }
}