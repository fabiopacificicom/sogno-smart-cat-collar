@php
    $statusBg = [
        'healthy' => 'bg-teal-50 border-teal-200',
        'warning' => 'bg-amber-50 border-amber-200',
        'critical' => 'bg-red-50 border-red-200',
    ];
    $statusEmoji = ['healthy' => '🟢', 'warning' => '🟡', 'critical' => '🔴'];
    $statusText = ['healthy' => 'text-teal-500', 'warning' => 'text-amber-500', 'critical' => 'text-red-500'];
@endphp

<div>
    <!-- Critical Alert Banner -->
    @if($criticalCount > 0)
        <div class="bg-red-500 text-white px-4 py-2 text-center text-sm font-medium rounded-xl mb-6">
            🚨 {{ $criticalCount }} cat(s) in critical condition — <a href="#cat-grid" class="underline">View Details</a>
        </div>
    @endif

    <!-- Cat Cards -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800">Your Cats</h2>
        <a href="{{ route('settings', ['tab' => 'cats']) }}" class="text-sm text-orange-600 hover:text-orange-700 font-medium">+ Add Cat</a>
    </div>

    <div id="cat-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @forelse($cats as $cat)
            <a href="{{ route('cat-detail', $cat) }}" class="block rounded-xl border-2 {{ $statusBg[$cat->status] ?? 'bg-gray-50 border-gray-200' }} p-5 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">🐱</span>
                        <div>
                            <h3 class="font-bold text-gray-800">{{ $cat->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $cat->breed ?? 'Unknown breed' }}</p>
                        </div>
                    </div>
                    <span class="text-xl">{{ $statusEmoji[$cat->status] ?? '⚪' }}</span>
                </div>
                @if($cat->latestReading)
                    <div class="grid grid-cols-3 gap-3 mt-4">
                        <div class="text-center">
                            <p class="text-xs text-gray-500">🌡️ Temp</p>
                            <p class="font-bold {{ $cat->latestReading->temperature > 39.0 ? 'text-red-500' : 'text-gray-800' }}">{{ $cat->latestReading->temperature }}°C</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500">❤️ BPM</p>
                            <p class="font-bold {{ $cat->latestReading->bpm > 220 ? 'text-red-500' : 'text-gray-800' }}">{{ $cat->latestReading->bpm }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500">🏃 Activity</p>
                            <p class="font-bold capitalize text-gray-800">{{ $cat->latestReading->activity }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Last reading: {{ $cat->latestReading->read_at->diffForHumans() }}</p>
                @else
                    <p class="text-sm text-gray-400 mt-4 text-center">No readings yet</p>
                @endif
            </a>
        @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                <p class="text-4xl mb-2">🐱</p>
                <p>No cats yet. Add one in Settings.</p>
            </div>
        @endforelse
    </div>

    <!-- Alert Log -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800">Recent Alerts</h2>
    </div>

    <div class="mb-8">
        @forelse($activeAlerts as $alert)
            <div class="flex items-start gap-3 py-3 {{ $alert->type === 'critical' ? 'bg-red-50' : ($alert->type === 'warning' ? 'bg-amber-50' : 'bg-gray-50') }} px-4 rounded-lg mb-2">
                <span class="text-lg">{{ $alert->type === 'critical' ? '🔴' : ($alert->type === 'warning' ? '🟡' : 'ℹ️') }}</span>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800">
                        <a href="{{ route('cat-detail', $alert->cat_id) }}" class="hover:underline">{{ $alert->cat->name }}</a>: {{ $alert->message }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $alert->created_at->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-4 text-center">No active alerts 🎉</p>
        @endforelse
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-orange-500">{{ $cats->count() }}</p>
            <p class="text-xs text-gray-500">Cats Monitored</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $criticalCount + $warningCount }}</p>
            <p class="text-xs text-gray-500">Active Alerts</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-teal-500">{{ $readingsToday }}</p>
            <p class="text-xs text-gray-500">Readings Today</p>
        </div>
    </div>
</div>
