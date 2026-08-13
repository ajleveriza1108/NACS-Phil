<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Support\GalleryContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class GalleryContentController extends Controller
{
    public function edit(): View
    {
        return view('admin.gallery-content.edit',['content'=>SiteContent::valuesFor('gallery',GalleryContent::defaults())]);
    }
    public function update(Request $request): RedirectResponse
    {
        $v=$request->validate([
            'hero_badge'=>['required','string','max:100'],
            'hero_heading'=>['required','string','max:160'],
            'hero_highlight'=>['required','string','max:160'],
            'hero_lead'=>['required','string','max:1000'],
            'listing_heading'=>['required','string','max:180'],
            'listing_text'=>['required','string','max:1800'],
            'empty_heading'=>['required','string','max:180'],
            'empty_text'=>['required','string','max:1200'],
            'privacy_heading'=>['required','string','max:180'],
            'privacy_text'=>['required','string','max:1800'],
            'privacy_button'=>['required','string','max:50'],
        ]);
        SiteContent::storeValues('gallery',$v);
        return back()->with('success','Gallery page settings saved. Photo records were not changed.');
    }
}