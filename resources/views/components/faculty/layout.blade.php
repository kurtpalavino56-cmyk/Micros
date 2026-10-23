@props([
    'title' => 'Faculty',
    'shell' => true,
    'useAppCss' => false,
    'useAppJs' => false,
])

<!DOCTYPE html>
<html lang="en" data-faculty-page="{{ $attributes->get('data-faculty-page') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @if ($useAppCss && $useAppJs)
        @vite(['resources/css/app.css', 'resources/css/faculty.css', 'resources/js/app.js'])
    @elseif ($useAppCss)
        @vite(['resources/css/app.css', 'resources/css/faculty.css'])
    @elseif ($useAppJs)
        @vite(['resources/css/faculty.css', 'resources/js/app.js'])
    @else
        @vite('resources/css/faculty.css')
    @endif
    {{ $head ?? '' }}
</head>
<body {{ $attributes }}>
    @if ($shell)
        @include('components.authenticated-topbar')
        <div class="layout faculty-sidebar-layout">
            @include('components.faculty-sidebar')
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
    {{ $scripts ?? '' }}
</body>
</html>
