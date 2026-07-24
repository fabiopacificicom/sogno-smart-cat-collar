<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Smart Cat Collar') }}</title>
    <link rel="icon" href="{{ asset('icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 antialiased">

    <!-- Top Bar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="text-2xl">🐱</span>
                    <h1 class="text-lg font-bold text-gray-800">Smart Cat Collar</h1>
                </a>
                <span class="text-xs text-gray-400">v1.0.0</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-xl hover:scale-110 transition-transform" title="Dashboard">📊</a>
                <a href="{{ route('settings') }}" class="text-xl hover:scale-110 transition-transform" title="Settings">⚙️</a>
            </div>
        </div>
    </header>

    <!-- Flash / alert banner area -->
    @if(session('status'))
        <div class="bg-teal-500 text-white px-4 py-2 text-center text-sm font-medium">
            {{ session('status') }}
        </div>
    @endif

    <main class="max-w-5xl mx-auto px-4 py-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
