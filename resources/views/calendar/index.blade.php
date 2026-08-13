@extends('layouts.public', ['title' => 'Academic Calendar'])

@section('content')
<section class="nacs12-hero">
    <div class="nacs11-shell">
        <span class="nacs12-kicker">Dates to Remember</span>
        <h1>Academic Calendar</h1>
        <p>Published academic dates, holidays, examinations, admissions periods, meetings, activities, and recognition events.</p>
    </div>
</section>

<section class="nacs12-section">
    <div class="nacs11-shell">
        <form class="nacs12-filter-form" method="GET">
            <label>School Year
                <select name="school_year">
                    <option value="">All</option>
                    @foreach($schoolYears as $year)<option value="{{ $year }}" @selected($schoolYear === $year)>{{ $year }}</option>@endforeach
                </select>
            </label>
            <label>Category
                <select name="category">
                    <option value="">All</option>
                    @foreach($categories as $value=>$label)<option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>@endforeach
                </select>
            </label>
            <button class="nacs11-button nacs11-button--primary">Filter</button>
        </form>

        <div class="nacs12-timeline">
            @forelse($entries as $entry)
                <article>
                    <div class="nacs12-date">
                        <strong>{{ $entry->starts_at->format('M') }}</strong>
                        <span>{{ $entry->starts_at->format('d') }}</span>
                    </div>
                    <div>
                        <span class="nacs12-chip">{{ $categories[$entry->category] ?? ucfirst($entry->category) }}</span>
                        <h2>{{ $entry->title }}</h2>
                        <p>{{ $entry->is_all_day ? 'All day' : $entry->starts_at->format('g:i A').' - '.$entry->ends_at->format('g:i A') }}</p>
                        @if($entry->description)<p>{{ $entry->description }}</p>@endif
                        @if($entry->school_year)<small>School Year {{ $entry->school_year }}</small>@endif
                    </div>
                </article>
            @empty
                <div class="nacs12-empty">No academic calendar entries match this filter.</div>
            @endforelse
        </div>

        {{ $entries->links() }}
    </div>
</section>
@endsection
