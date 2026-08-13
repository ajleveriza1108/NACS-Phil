<?php
namespace App\Support;
class GalleryContent
{
    public static function defaults(): array
    {
        return [
            'hero_badge'=>'School Gallery',
            'hero_heading'=>'A glimpse into',
            'hero_highlight'=>'life at NACS-Phil.',
            'hero_lead'=>'Explore school-approved photographs from learning activities, celebrations, programs, and community moments.',
            'listing_heading'=>'Moments from our school community',
            'listing_text'=>'Only photographs marked published with recorded school authorization and appropriate consent can appear in this public gallery.',
            'empty_heading'=>'No approved images yet.',
            'empty_text'=>'When authorized staff publish school-approved photographs, they will appear here automatically.',
            'privacy_heading'=>'Student privacy comes before publicity.',
            'privacy_text'=>'The public gallery shows only approved images with recorded consent confirmation. Staff should continue following school policy before publishing photographs.',
            'privacy_button'=>'Read Privacy Information',
        ];
    }
}