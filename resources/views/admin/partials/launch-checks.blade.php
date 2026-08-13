<div class="p15-check-list">
    @foreach($checks as $check)
        <div class="p15-check {{ $check['passed'] ? 'is-pass' : 'is-pending' }}">
            <span class="p15-check-icon" aria-hidden="true">{{ $check['passed'] ? 'OK' : '!' }}</span>
            <span>
                <strong>{{ $check['label'] }}</strong>
                <small>{{ $check['detail'] }}</small>
            </span>
        </div>
    @endforeach
</div>
