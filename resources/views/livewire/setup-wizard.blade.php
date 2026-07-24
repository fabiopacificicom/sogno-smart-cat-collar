@php
    $progress = ($this->step / $this->totalSteps) * 100;
@endphp

<div>
    <!-- Progress Bar -->
    <div class="fixed top-0 left-0 right-0 h-1 bg-gray-200 z-50">
        <div class="h-full bg-orange-500 transition-all duration-300" style="width: {{ $progress }}%"></div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-12">

        <!-- Step Indicators -->
        <div class="flex items-center justify-center gap-2 mb-8">
            @for($i = 1; $i <= $this->totalSteps; $i++)
                <div class="w-8 h-8 rounded-full {{ $i <= $this->step ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center text-sm font-bold">{{ $i }}</div>
                @if($i < $this->totalSteps)
                    <div class="w-6 h-0.5 {{ $i < $this->step ? 'bg-orange-500' : 'bg-gray-200' }}"></div>
                @endif
            @endfor
        </div>

        <!-- Step 1: Welcome -->
        @if($this->step === 1)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
                <div class="text-6xl mb-4">🐱</div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Welcome to Smart Cat Collar</h1>
                <p class="text-gray-500 mb-6">Monitor your cat's health in real time. Temperature, heart rate, and activity tracking — all from your desktop.</p>
                <div class="bg-orange-50 rounded-xl p-4 mb-6 text-left">
                    <p class="text-sm text-orange-800 font-medium">This setup takes about 2 minutes. You'll:</p>
                    <ul class="text-sm text-orange-700 mt-2 space-y-1">
                        <li>🐱 Add your first cat</li>
                        <li>📡 Choose a data source</li>
                        <li>⚠️ Set alert thresholds</li>
                        <li>📊 Optionally load demo data</li>
                    </ul>
                </div>
            </div>
        @endif

        <!-- Step 2: Add Cat -->
        @if($this->step === 2)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <div class="text-4xl mb-4 text-center">🐾</div>
                <h2 class="text-xl font-bold text-gray-800 mb-1 text-center">Add Your First Cat</h2>
                <p class="text-gray-500 text-sm mb-6 text-center">You can add more cats later from Settings.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" wire:model="catName" placeholder="Your cat's name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                        @error('catName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Breed</label>
                        <select wire:model="catBreed" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none bg-white">
                            <option>Domestic Shorthair</option>
                            <option>Persian</option>
                            <option>Siamese</option>
                            <option>Maine Coon</option>
                            <option>British Shorthair</option>
                            <option>Ragdoll</option>
                            <option>Bengal</option>
                            <option>Sphynx</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Birth Year (optional)</label>
                        <input type="number" wire:model="birthYear" placeholder="2020" min="2000" max="2026" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 3: Choose Provider -->
        @if($this->step === 3)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <div class="text-4xl mb-4 text-center">📡</div>
                <h2 class="text-xl font-bold text-gray-800 mb-1 text-center">Choose Data Source</h2>
                <p class="text-gray-500 text-sm mb-6 text-center">How should the app receive sensor data from the collar?</p>

                <div class="space-y-3">
                    @foreach(app(\App\Services\ProviderManager::class)->all() as $provider)
                        <label class="flex items-start gap-3 p-4 border-2 {{ $this->provider === $provider->getKey() ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300' }} rounded-xl cursor-pointer transition-colors">
                            <input type="radio" wire:model="provider" value="{{ $provider->getKey() }}" class="mt-1 accent-orange-500">
                            <div class="flex-1">
                                <span class="font-medium text-gray-800">{{ $provider->getName() }}</span>
                                @if($provider->getKey() === 'mock')
                                    <span class="ml-2 text-xs bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full">Recommended</span>
                                @endif
                                <p class="text-sm text-gray-500 mt-1">
                                    @if($provider->getKey() === 'mock')
                                        Generates realistic fake data. Great for testing before the collar is assembled.
                                    @elseif($provider->getKey() === 'direct_api')
                                        Collar sends data directly to the desktop app via a tunnel URL.
                                    @elseif($provider->getKey() === 'telegram')
                                        Receive collar data via Telegram Bot API. Fallback option.
                                    @endif
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Step 4: Thresholds -->
        @if($this->step === 4)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <div class="text-4xl mb-4 text-center">⚠️</div>
                <h2 class="text-xl font-bold text-gray-800 mb-1 text-center">Set Alert Thresholds</h2>
                <p class="text-gray-500 text-sm mb-4 text-center">When should the app alert you? You can change these later.</p>

                <button wire:click="useDefaults" class="w-full mb-4 text-sm text-orange-600 hover:text-orange-700 font-medium py-2 border border-orange-200 rounded-lg hover:bg-orange-50 transition-colors">
                    ✨ Use recommended values
                </button>

                <div class="space-y-4">
                    <div class="bg-red-50 rounded-xl p-4">
                        <h3 class="text-sm font-bold text-red-800 mb-3">🌡️ Temperature</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-red-600">Warning (°C)</label>
                                <input type="number" wire:model="tempWarning" step="0.1" class="w-full px-3 py-2 border border-red-200 rounded-lg text-sm focus:ring-2 focus:ring-red-400 outline-none">
                            </div>
                            <div>
                                <label class="text-xs text-red-600">Critical (°C)</label>
                                <input type="number" wire:model="tempCritical" step="0.1" class="w-full px-3 py-2 border border-red-200 rounded-lg text-sm focus:ring-2 focus:ring-red-400 outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50 rounded-xl p-4">
                        <h3 class="text-sm font-bold text-amber-800 mb-3">❤️ Heart Rate (BPM)</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-amber-600">Warning</label>
                                <input type="number" wire:model="bpmWarning" class="w-full px-3 py-2 border border-amber-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-400 outline-none">
                            </div>
                            <div>
                                <label class="text-xs text-amber-600">Critical</label>
                                <input type="number" wire:model="bpmCritical" class="w-full px-3 py-2 border border-amber-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-400 outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 5: Demo Data + Done -->
        @if($this->step === 5)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
                <div class="text-6xl mb-4">🎉</div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Almost Done!</h2>
                <p class="text-gray-500 mb-6">Your Smart Cat Collar companion is ready to set up.</p>

                <!-- Demo data choice -->
                <div class="bg-gray-50 rounded-xl p-4 text-left mb-6">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="seedDemoData" class="mt-1 w-5 h-5 accent-orange-500 rounded">
                        <div class="flex-1">
                            <span class="font-medium text-gray-800">📊 Load demo data</span>
                            <p class="text-sm text-gray-500 mt-1">
                                Populates the app with 7 sample cats (Antifa, Grogu, Mando, etc.), sensor readings, and alerts so you can see how it works immediately.
                                Uncheck to start with just your cat and an empty dashboard.
                            </p>
                        </div>
                    </label>
                </div>

                <!-- Summary -->
                <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2">
                    <p class="text-sm"><span class="text-gray-500">Cat:</span> <span class="font-medium text-gray-800">{{ $catName ?: '(using demo cats)' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Data source:</span> <span class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $provider)) }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Temp critical:</span> <span class="font-medium text-red-600">{{ $tempCritical }}°C</span></p>
                    <p class="text-sm"><span class="text-gray-500">BPM critical:</span> <span class="font-medium text-amber-600">{{ $bpmCritical }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Demo data:</span> <span class="font-medium {{ $seedDemoData ? 'text-teal-600' : 'text-gray-400' }}">{{ $seedDemoData ? 'Yes — load 7 sample cats' : 'No — start fresh' }}</span></p>
                </div>
            </div>
        @endif

        <!-- Navigation -->
        <div class="flex justify-between mt-6">
            @if($this->step > 1)
                <button wire:click="prevStep" class="px-6 py-2.5 text-gray-600 hover:text-gray-800 font-medium rounded-lg hover:bg-gray-100 transition-colors">Back</button>
            @else
                <div></div>
            @endif
            <button wire:click="nextStep" class="px-8 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors">
                {{ $this->step === $this->totalSteps ? 'Start Monitoring 🐱' : 'Next' }}
            </button>
        </div>
    </div>
</div>
