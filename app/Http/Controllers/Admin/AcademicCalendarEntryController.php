<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendarEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicCalendarEntryController extends Controller
{
    public function index(Request $request): View
    {
        $schoolYear = trim($request->string('school_year')->toString());

        $query = AcademicCalendarEntry::query()->orderBy('starts_at');

        if ($schoolYear !== '') {
            $query->where('school_year', $schoolYear);
        }

        return view('admin.calendar.index', [
            'entries' => $query->paginate(30)->withQueryString(),
            'schoolYear' => $schoolYear,
        ]);
    }

    public function create(): View
    {
        return view('admin.calendar.form', ['categories' => AcademicCalendarEntry::CATEGORIES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by_user_id'] = $request->user()?->id;
        AcademicCalendarEntry::create($data);

        return redirect()->route('admin.calendar.index')->with('success', 'Academic calendar entry created.');
    }

    public function edit(AcademicCalendarEntry $calendar): View
    {
        return view('admin.calendar.form', [
            'entry' => $calendar,
            'categories' => AcademicCalendarEntry::CATEGORIES,
        ]);
    }

    public function update(Request $request, AcademicCalendarEntry $calendar): RedirectResponse
    {
        $calendar->update($this->validated($request));

        return redirect()->route('admin.calendar.index')->with('success', 'Academic calendar entry updated.');
    }

    public function destroy(AcademicCalendarEntry $calendar): RedirectResponse
    {
        $calendar->delete();

        return redirect()->route('admin.calendar.index')->with('success', 'Academic calendar entry archived.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required','string','max:180'],
            'category' => ['required', Rule::in(array_keys(AcademicCalendarEntry::CATEGORIES))],
            'description' => ['nullable','string','max:5000'],
            'starts_at' => ['required','date'],
            'ends_at' => ['required','date','after_or_equal:starts_at'],
            'school_year' => ['nullable','string','max:30'],
        ]);

        $data['is_all_day'] = $request->boolean('is_all_day');
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
