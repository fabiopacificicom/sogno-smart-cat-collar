@php
    $statusBg = ['healthy' => 'bg-teal-100 text-teal-600', 'warning' => 'bg-amber-100 text-amber-600', 'critical' => 'bg-red-100 text-red-600'];
    $statusLabel = ['healthy' => 'Healthy', 'warning' => 'Warning', 'critical' => 'Critical'];
@endphp

<div>
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dashboard') }}" class="text-xl hover:scale-110 transition-transform">←</a>
        <span class="text-2xl">🐱</span>
        <h1 class="text-lg font-bold text-gray-800">{{ $cat->name }}</h1>
        <span class="text-xs {{ $statusBg[$cat->status] ?? 'bg-gray-100 text-gray-600' }} px-2 py-0.5 rounded-full font-medium">{{ $statusLabel[$cat->status] ?? 'Unknown' }}</span>
    </div>

    @if($latest)
        <!-- Vitals Grid -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-xs text-gray-500 mb-1">🌡️ Temperature</p>
                <p class="text-3xl font-bold {{ $latest->temperature > 39.5 ? 'text-red-500' : ($latest->temperature > 39.0 ? 'text-amber-500' : 'text-teal-500') }}">{{ $latest->temperature }}°C</p>
                <p class="text-xs text-gray-400 mt-1">{{ $latest->read_at->diffForHumans() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-xs text-gray-500 mb-1">❤️ Heart Rate</p>
                <p class="text-3xl font-bold {{ $latest->bpm > 250 ? 'text-red-500' : ($latest->bpm > 220 ? 'text-amber-500' : 'text-teal-500') }}">{{ $latest->bpm }}</p>
                <p class="text-xs text-gray-400 mt-1">bpm</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-xs text-gray-500 mb-1">🏃 Activity</p>
                <p class="text-3xl font-bold text-gray-800 capitalize">{{ $latest->activity }}</p>
                <p class="text-xs text-gray-400 mt-1">level</p>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400 mb-6">
            <p class="text-4xl mb-2">📊</p>
            <p>No sensor readings yet for {{ $cat->name }}.</p>
        </div>
    @endif

    <!-- Thresholds -->
    @if($tempThreshold && $bpmThreshold)
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <h3 class="text-sm font-bold text-gray-700 mb-2">Current Thresholds</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">🌡️ Temp Warning: <span class="font-medium text-amber-600">{{ $tempThreshold->warning_value }}°C</span></p>
                    <p class="text-gray-500">🌡️ Temp Critical: <span class="font-medium text-red-600">{{ $tempThreshold->critical_value }}°C</span></p>
                </div>
                <div>
                    <p class="text-gray-500">❤️ BPM Warning: <span class="font-medium text-amber-600">{{ $bpmThreshold->warning_value }}</span></p>
                    <p class="text-gray-500">❤️ BPM Critical: <span class="font-medium text-red-600">{{ $bpmThreshold->critical_value }}</span></p>
                </div>
            </div>
        </div>
    @endif

    <!-- Readings History -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <h3 class="text-sm font-bold text-gray-700 mb-3">Recent Readings</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-2 text-left text-xs text-gray-500 font-medium">Time</th>
                        <th class="py-2 text-left text-xs text-gray-500 font-medium">Temp</th>
                        <th class="py-2 text-left text-xs text-gray-500 font-medium">BPM</th>
                        <th class="py-2 text-left text-xs text-gray-500 font-medium">Activity</th>
                        <th class="py-2 text-left text-xs text-gray-500 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cat->sensorReadings as $reading)
                        @php
                            $rStatus = $reading->temperature > 39.5 ? '🔴' : ($reading->temperature > 39.0 ? '🟡' : '🟢');
                        @endphp
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-sm text-gray-600">{{ $reading->read_at->format('H:i') }}</td>
                            <td class="py-2 text-sm {{ $reading->temperature > 39.0 ? 'text-red-500 font-bold' : 'text-gray-800' }}">{{ $reading->temperature }}°C</td>
                            <td class="py-2 text-sm {{ $reading->bpm > 220 ? 'text-red-500 font-bold' : 'text-gray-800' }}">{{ $reading->bpm }}</td>
                            <td class="py-2 text-sm capitalize text-gray-800">{{ $reading->activity }}</td>
                            <td class="py-2 text-sm">{{ $rStatus }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-gray-400 text-sm">No readings yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alert History -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <h3 class="text-sm font-bold text-gray-700 mb-3">Alert History</h3>
        <div>
            @forelse($cat->alerts as $alert)
                <div class="flex items-start gap-3 py-2 border-b border-gray-100">
                    <span>{{ $alert->type === 'critical' ? '🔴' : ($alert->type === 'warning' ? '🟡' : 'ℹ️') }}</span>
                    <div>
                        <p class="text-sm text-gray-800">{{ $alert->message }}</p>
                        <p class="text-xs text-gray-400">{{ $alert->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-2">No alerts for this cat 🎉</p>
            @endforelse
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-3">
        <a href="{{ route('settings') }}" class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">⚙️ Edit in Settings</a>
    </div>
</div>
