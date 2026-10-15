@props(['message', 'type' => 'success'])

@php
    $type = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
@endphp

@if (filled($message))
    @vite(['resources/css/flash-toast.css', 'resources/js/flash-toast.js'])

    <div class="flash-toast-region" data-flash-toast-region aria-live="{{ $type === 'error' ? 'assertive' : 'polite' }}" aria-relevant="additions">
        <div class="flash-toast flash-toast--{{ $type }}" data-flash-toast role="{{ $type === 'error' ? 'alert' : 'status' }}" tabindex="-1">
            <span class="flash-toast__icon" aria-hidden="true">
                @if ($type === 'success')
                    <svg viewBox="0 0 20 20" fill="none"><path d="m5 10 3.2 3.2L15 6.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                @elseif ($type === 'error')
                    <svg viewBox="0 0 20 20" fill="none"><path d="M10 5.5v5M10 14h.01M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
                @elseif ($type === 'warning')
                    <svg viewBox="0 0 20 20" fill="none"><path d="M10 6v4.5M10 13.5h.01M8.3 3.8 2.9 13.2A1.5 1.5 0 0 0 4.2 15.5h11.6a1.5 1.5 0 0 0 1.3-2.3l-5.4-9.4a1.9 1.9 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                @else
                    <svg viewBox="0 0 20 20" fill="none"><path d="M10 8.5v4M10 5.8h.01M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
                @endif
            </span>
            <span class="flash-toast__message">{{ $message }}</span>
            <button class="flash-toast__dismiss" type="button" data-dismiss-flash-toast aria-label="Dismiss message">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
            </button>
            <span class="flash-toast__timer" data-flash-toast-timer aria-hidden="true"></span>
        </div>
    </div>
@endif
