@if(config('services.turnstile.enabled'))
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce

    @if(filled(config('services.turnstile.site_key')))
        <div
            class="cf-turnstile"
            data-sitekey="{{ config('services.turnstile.site_key') }}"
            data-action="{{ $action }}"
            data-appearance="interaction-only"
            data-theme="auto"
            data-size="flexible"
            data-retry="auto"
            data-refresh-expired="auto"
            data-refresh-timeout="auto"
        ></div>
        <small class="nacs-turnstile-note">
            Security verification runs automatically when possible and asks for interaction only when needed.
        </small>
    @else
        <div role="alert">
            Security verification is not configured yet. Please contact the school if this appears on the live website.
        </div>
    @endif
@endif
