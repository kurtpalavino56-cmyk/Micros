@props([
    'title' => 'UpSkill | PSU Microcredentials',
    'shell' => 'bare',
    'page' => null,
])

@php
    $supportedShells = ['student', 'faculty', 'admin', 'public', 'auth', 'bare'];

    if (! in_array($shell, $supportedShells, true)) {
        throw new InvalidArgumentException("Unsupported layout shell [{$shell}].");
    }

    $pageClass = filled($page) ? 'page-'.\Illuminate\Support\Str::slug($page) : '';
    $layoutUser = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="UpSkill – The Official Platform for PSU Microcredentials">
    <title>{{ $title }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="app-shell app-shell--{{ $shell }} {{ $pageClass }}">
    @if ($shell === 'public')
        @include('components.navbar')
    @elseif (in_array($shell, ['student', 'faculty', 'admin'], true))
        @include('components.authenticated-topbar', ['user' => $layoutUser])
    @endif

    @if ($shell === 'student')
        @if ($page === 'course-player')
            <main class="app-main app-main--course-player">{{ $slot }}</main>
        @else
            <div class="layout student-sidebar-layout">
                @include('components.student-sidebar', ['user' => $layoutUser])
                <main class="main app-main">{{ $slot }}</main>
            </div>
        @endif
    @elseif ($shell === 'faculty')
        <div class="layout faculty-sidebar-layout">
            @include('components.faculty-sidebar', ['user' => $layoutUser])
            <main class="main app-main">{{ $slot }}</main>
        </div>
    @elseif ($shell === 'admin')
        <div class="layout admin-layout">
            @include('components.admin-sidebar', ['user' => $layoutUser])
            <main class="main app-main">{{ $slot }}</main>
        </div>
    @elseif ($shell === 'public')
        <main class="app-main">{{ $slot }}</main>
        @include('components.footer')
    @elseif ($shell === 'auth')
        <main class="app-main app-main--auth">{{ $slot }}</main>
    @else
        <main class="app-main">{{ $slot }}</main>
    @endif

    @include('components.responsive')
    @stack('scripts')
</body>
</html>