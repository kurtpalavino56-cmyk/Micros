@props(['message'])

@if (filled($message))
    @vite(['resources/css/flash-toast.css', 'resources/js/flash-toast.js'])

    <div class="flash-toast-region" data-flash-toast-region aria-live="polite" aria-relevant="additions">
        <div class="flash-toast" data-flash-toast role="status" tabindex="-1">
            <span class="flash-toast__message">{{ $message }}</span>
            <button class="flash-toast__dismiss" type="button" data-dismiss-flash-toast aria-label="Dismiss message">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
            </button>
        </div>
    </div>
@endif
