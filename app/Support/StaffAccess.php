<?php

namespace App\Support;

use Illuminate\Support\Str;

final class StaffAccess
{
    public const ROLE_LABELS = [
        'super_admin' => 'Super Administrator',
        'principal' => 'Principal / School Admin',
        'teacher' => 'Teacher / Content Contributor',
        'frontend_editor' => 'Frontend Editor',
        'text_editor' => 'Text / Content Editor',
        'news_editor' => 'News / Posting Editor',
        'events_editor' => 'Events Editor',
        'media_editor' => 'Media / Gallery Editor',
        'admissions_editor' => 'Admissions Editor',
        'documents_editor' => 'Documents / Resources Editor',
    ];

    public const ROLE_DESCRIPTIONS = [
        'super_admin' => 'Full administration, staff access, security oversight, SIS, website, and system health.',
        'principal' => 'Broad school administration without authority to manage Super Administrator accounts.',
        'teacher' => 'Student records plus the existing reviewed announcement, event, and media contribution workflow.',
        'frontend_editor' => 'Public website pages, faculty presentation, branding, SEO, and visual content configuration. No code or deployment access.',
        'text_editor' => 'Public page wording and faculty text. No branding, security, SIS, or applicant records.',
        'news_editor' => 'Announcements and the public News page only.',
        'events_editor' => 'School events, academic calendar, and the public Events page.',
        'media_editor' => 'Approved gallery photos, media library, Facebook media, and the public Gallery page.',
        'admissions_editor' => 'Admissions applications, family inquiries, and the public Admissions page.',
        'documents_editor' => 'Public school documents and downloadable resources.',
    ];

    private const PRINCIPAL_PERMISSIONS = [
        'students.manage',
        'news.manage',
        'events.manage',
        'media.manage',
        'governance.manage',
        'faculty.manage',
        'documents.manage',
        'calendar.manage',
        'admissions.manage',
        'website.home',
        'website.about',
        'website.programs',
        'website.admissions',
        'website.news',
        'website.events',
        'website.gallery',
        'website.contact',
        'branding.manage',
        'header.manage',
        'seo.manage',
        'settings.manage',
    ];

    private const PERMISSIONS = [
        'principal' => self::PRINCIPAL_PERMISSIONS,
        'teacher' => [
            'students.manage',
            'news.manage',
            'events.manage',
            'media.manage',
        ],
        'frontend_editor' => [
            'faculty.manage',
            'website.home',
            'website.about',
            'website.programs',
            'website.admissions',
            'website.news',
            'website.events',
            'website.gallery',
            'website.contact',
            'branding.manage',
            'header.manage',
            'seo.manage',
        ],
        'text_editor' => [
            'faculty.manage',
            'website.home',
            'website.about',
            'website.programs',
            'website.admissions',
            'website.news',
            'website.events',
            'website.gallery',
            'website.contact',
            'header.manage',
        ],
        'news_editor' => [
            'news.manage',
            'website.news',
        ],
        'events_editor' => [
            'events.manage',
            'calendar.manage',
            'website.events',
        ],
        'media_editor' => [
            'media.manage',
            'website.gallery',
        ],
        'admissions_editor' => [
            'admissions.manage',
            'website.admissions',
        ],
        'documents_editor' => [
            'documents.manage',
        ],
    ];

    public static function roles(): array
    {
        return self::ROLE_LABELS;
    }

    public static function descriptions(): array
    {
        return self::ROLE_DESCRIPTIONS;
    }

    public static function isKnownRole(?string $role): bool
    {
        return is_string($role) && array_key_exists($role, self::ROLE_LABELS);
    }

    public static function roleHasPermission(?string $role, string $permission): bool
    {
        if ($role === 'super_admin') {
            return true;
        }

        return in_array($permission, self::PERMISSIONS[$role] ?? [], true);
    }

    public static function staffingPlan(): array
    {
        return [
            'super_admin' => [
                'label' => 'Super Administrators',
                'target' => 2,
                'purpose' => 'Two protected administrators for continuity, recovery, staff access, and security oversight.',
            ],
            'frontend_editor' => [
                'label' => 'Frontend Editor',
                'target' => 1,
                'purpose' => 'Public-page presentation, branding, SEO, and visual content configuration.',
            ],
            'text_editor' => [
                'label' => 'Text / Content Editor',
                'target' => 1,
                'purpose' => 'Approved wording for Home, About, Programs, Admissions, News, Events, Gallery, and Contact.',
            ],
            'news_editor' => [
                'label' => 'News / Posting Editor',
                'target' => 1,
                'purpose' => 'Announcements, notices, and News-page content.',
            ],
            'events_editor' => [
                'label' => 'Events Editor',
                'target' => 1,
                'purpose' => 'Events, academic calendar items, and Events-page content.',
            ],
            'media_editor' => [
                'label' => 'Media / Gallery Editor',
                'target' => 1,
                'purpose' => 'Consent-approved gallery, media library, and public media.',
            ],
            'admissions_editor' => [
                'label' => 'Admissions Editor',
                'target' => 1,
                'purpose' => 'Applicant workflow, inquiry CRM, and Admissions-page content.',
            ],
            'documents_editor' => [
                'label' => 'Documents / Resources Editor',
                'target' => 1,
                'purpose' => 'Public forms, handbooks, downloads, and school resources.',
            ],
        ];
    }

    public static function roleSortOrder(?string $role): int
    {
        $order = [
            'super_admin',
            'principal',
            'frontend_editor',
            'text_editor',
            'news_editor',
            'events_editor',
            'media_editor',
            'admissions_editor',
            'documents_editor',
            'teacher',
        ];

        $position = array_search($role, $order, true);

        return $position === false ? 99 : $position + 1;
    }

    public static function officialEmailDomain(): ?string
    {
        $domain = Str::lower(trim((string) config('nacs.school_email_domain', '')));
        $domain = ltrim($domain, '@');

        return $domain !== '' ? $domain : null;
    }
}
