<?php
namespace App\Http\Controllers;
use App\Models\GalleryItem;
use Illuminate\View\View;
class GalleryController extends Controller
{
    public function index(): View
    {
        $category=trim((string)request()->query('category',''));
        $categories=GalleryItem::published()
            ->whereNotNull('category')->where('category','<>','')
            ->select('category')->distinct()->orderBy('category')->pluck('category');
        $query=GalleryItem::published()->orderBy('sort_order')->latest('taken_at');
        if($category!==''){$query->where('category',$category);}
        return view('gallery.index',[
            'galleryItems'=>$query->paginate(18)->withQueryString(),
            'galleryCategories'=>$categories,
            'activeCategory'=>$category,
        ]);
    }
}