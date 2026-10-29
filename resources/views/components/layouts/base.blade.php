@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ config('safora.locales.'.app()->getLocale().'.dir', 'rtl') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' | ' : '' }}SAFORA</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 47'%3E%3Crect width='35' height='35' rx='8' fill='%23F02B18'/%3E%3Crect x='13' y='12' width='35' height='35' rx='8' fill='%23FD8118' fill-opacity='.92'/%3E%3C/svg%3E">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body {{ $attributes->merge(['class' => 'min-h-screen bg-canvas font-sans text-ink antialiased']) }}>
    {{ $slot }}
</body>
</html>
