<?php

namespace App\Http\Controllers;

use App\Models\FacultyProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacultyController extends Controller
{
    public function index(Request $request): View
    {
        $department = trim($request->string('department')->toString());

        $query = FacultyProfile::published()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($department !== '') {
            $query->where('department', $department);
        }

        return view('faculty.index', [
            'profiles' => $query->get(),
            'departments' => FacultyProfile::published()
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->distinct()
                ->orderBy('department')
                ->pluck('department'),
            'department' => $department,
        ]);
    }
}
