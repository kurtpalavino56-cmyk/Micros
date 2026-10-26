@props([
    'title' => 'Admin',
    'page' => 'admin-page',
    'bodyClass' => '',
    'user' => null,
    'useAuthUser' => true,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/css/admin.css'])
    {{ $head ?? '' }}
</head>
<body data-admin-page="{{ $page }}" @if($bodyClass !== '') class="{{ $bodyClass }}" @endif>
    @include('components.authenticated-topbar', ['user' => $useAuthUser ? auth()->user() : $user])
    @include('components.admin-sidebar')
    {{ $slot }}
    @include('components.responsive')
    {{ $scripts ?? '' }}
</body>
</html>
