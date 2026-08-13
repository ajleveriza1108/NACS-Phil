<?php
namespace Tests\Feature;
use App\Models\GalleryItem;
use App\Models\User;
use App\Support\GalleryContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class GalleryContentTest extends TestCase
{
    use RefreshDatabase;
    public function test_gallery_uses_phase_seven_design(): void
    {
        $this->get('/gallery')->assertOk()->assertSee('assets/phase7-gallery/gallery.css')->assertSee('viewport-fit=cover',false)->assertSee('School Gallery');
    }
    public function test_only_consent_confirmed_photos_are_public(): void
    {
        GalleryItem::create(['title'=>'Approved Photo','category'=>'School Life','image_path'=>'gallery/a.jpg','alt_text'=>'Approved','is_published'=>true,'sort_order'=>1,'consent_confirmed_at'=>now()]);
        GalleryItem::create(['title'=>'Private Photo','category'=>'School Life','image_path'=>'gallery/p.jpg','alt_text'=>'Private','is_published'=>true,'sort_order'=>2,'consent_confirmed_at'=>null]);
        $this->get('/gallery')->assertSee('Approved Photo')->assertDontSee('Private Photo');
    }
    public function test_gallery_filters_categories(): void
    {
        foreach(['Activities','Campus'] as $c){GalleryItem::create(['title'=>$c.' Photo','category'=>$c,'image_path'=>'gallery/x.jpg','alt_text'=>$c,'is_published'=>true,'sort_order'=>1,'consent_confirmed_at'=>now()]);}
        $this->get('/gallery?category=Activities')->assertSee('Activities Photo')->assertDontSee('Campus Photo');
    }
    public function test_admin_can_open_gallery_page_settings(): void
    {
        $a=User::factory()->create(['is_admin'=>true]);
        $this->actingAs($a)->get('/admin/gallery-content')->assertOk()->assertSee('Edit Gallery Page')->assertSee('Open Photos Manager');
    }
    public function test_admin_can_update_gallery_page_settings(): void
    {
        $a=User::factory()->create(['is_admin'=>true]);$c=GalleryContent::defaults();$c['hero_heading']='Life in Pictures';
        $this->actingAs($a)->patch('/admin/gallery-content',$c)->assertSessionHasNoErrors();
        $this->get('/gallery')->assertSee('Life in Pictures');
    }
}