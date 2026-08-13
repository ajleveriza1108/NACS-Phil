<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendarEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $schoolYear = trim($request->string('school_year')->toString());
        $category = trim($request->string('category')->toString());

        $query = AcademicCalendarEntry::published()->orderBy('starts_at');

        if ($schoolYear !== '') {
            $query->where('school_year', $schoolYear);
        }

        if ($category !== '' && array_key_exists($category, AcademicCalendarEntry::CATEGORIES)) {
            $query->where('category', $category);
        }

        return view('calendar.index', [
            'entries' => $query->paginate(30)->withQueryString(),
            'schoolYears' => AcademicCalendarEntry::published()
                ->whereNotNull('school_year')->distinct()->orderByDesc('school_year')->pluck('school_year'),
            'categories' => AcademicCalendarEntry::CATEGORIES,
            'schoolYear' => $schoolYear,
            'category' => $category,
        ]);
    }
}
