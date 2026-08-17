<?php
namespace Tests\Feature;

use App\Models\SiteContent;
use App\Support\HomeContent;
use App\Support\HomeEditorState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase58PremiumSmartEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_hidden_state_is_allowlisted_and_reversible(): void
    {
        HomeEditorState::setHiddenFields(['hero_badge','why_heading','fake','hero_badge']);
        $this->assertSame(['hero_badge','why_heading'], HomeEditorState::hiddenFields());
        HomeEditorState::setHiddenFields([]);
        $this->assertSame([], HomeEditorState::hiddenFields());
    }

    public function test_revision_restore_and_original_reset_are_nondestructive(): void
    {
        SiteContent::storeValues('home',['hero_badge'=>'Version A']);
        HomeEditorState::setHiddenFields(['hero_badge']);
        $key=HomeEditorState::recordRevision(null,'publish');
        SiteContent::storeValues('home',['hero_badge'=>'Version B']);
        HomeEditorState::setHiddenFields([]);
        $this->assertTrue(HomeEditorState::restoreRevision($key,null));
        $this->assertSame('Version A',SiteContent::valuesFor('home',HomeContent::defaults())['hero_badge']);
        $this->assertSame(['hero_badge'],HomeEditorState::hiddenFields());
        $this->assertGreaterThanOrEqual(3,count(HomeEditorState::revisions()));
        HomeEditorState::resetOriginal(null);
        $this->assertSame(HomeContent::defaults()['hero_badge'],SiteContent::valuesFor('home',HomeContent::defaults())['hero_badge']);
        $this->assertSame([],HomeEditorState::hiddenFields());
    }

    public function test_premium_editor_assets_are_scoped_away_from_dashboard(): void
    {
        $layout=(string)file_get_contents(resource_path('views/admin/layouts/app.blade.php'));
        $routes=(string)file_get_contents(base_path('routes/web.php'));
        $this->assertStringContainsString("request()->routeIs('admin.website-content.*')",$layout);
        $this->assertStringContainsString('assets/current/editor.css',$layout);
        $this->assertStringContainsString('assets/current/editor.js',$layout);
        $this->assertStringNotContainsString("asset('assets/phase",$layout);
        $this->assertStringContainsString('staff_permission:website.home',$routes);
        $this->assertStringNotContainsString("Route::get('/', [AdminWebsiteContentController",$routes);
    }

    public function test_premium_editor_has_recovery_hidden_undo_and_leave_protection(): void
    {
        $js=(string)file_get_contents(public_path('assets/current/editor.js'));
        $view=(string)file_get_contents(resource_path('views/admin/website-content/edit.blade.php'));
        foreach(['beforeunload','localStorage','nacs.ve.home.draft.v2','Ctrl+Z','Unsaved changes','Section hidden - Undo','data-ve-undo','data-ve-redo','data-ve-restore-all'] as $m)$this->assertStringContainsString($m,$js.$view);
        foreach(['Hidden Elements','Revision History','Reset Page','Save Draft','Publish Changes','Restore original page'] as $m)$this->assertStringContainsString($m,$view);
    }

    public function test_public_home_receives_allowlisted_hidden_fields(): void
    {
        $home=(string)file_get_contents(resource_path('views/home.blade.php'));
        $controller=(string)file_get_contents(app_path('Http/Controllers/HomeController.php'));
        $this->assertStringContainsString('$homeHiddenFields',$home);
        $this->assertStringContainsString('data-nacs-hidden-home-fields',$home);
        $this->assertStringContainsString('HomeEditorState::hiddenFields()',$controller);
    }
}
