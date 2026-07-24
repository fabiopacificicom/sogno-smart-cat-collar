<div x-data="{ saved: false, message: '' }" x-on:settings-saved.window="saved = true; message = $event.detail.message; setTimeout(() => saved = false, 3000)">

    <!-- Saved notification -->
    <div x-show="saved" x-transition class="bg-teal-500 text-white px-4 py-2 rounded-lg mb-4 text-sm font-medium">
        <span x-text="message"></span>
    </div>

    <!-- Tab Navigation -->
    <nav class="bg-white border border-gray-200 rounded-xl mb-6 overflow-x-auto">
        <div class="flex">
            @foreach(['general' => 'General', 'providers' => 'Data Providers', 'thresholds' => 'Alert Thresholds', 'cats' => 'Cats', 'about' => 'About'] as $tab => $label)
                <button wire:click="switchTab('{{ $tab }}')" class="px-4 py-3 text-sm font-medium whitespace-nowrap {{ $activeTab === $tab ? 'text-orange-600 border-b-2 border-orange-500' : 'text-gray-500 hover:text-gray-700' }}">{{ $label }}</button>
            @endforeach
        </div>
    </nav>

    <!-- General Tab -->
    @if($activeTab === 'general')
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Appearance</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Theme</p>
                            <p class="text-xs text-gray-500">Choose light, dark, or system default</p>
                        </div>
                        <select wire:model="theme" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                            <option value="system">System</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Language</p>
                            <p class="text-xs text-gray-500">App display language</p>
                        </div>
                        <select wire:model="language" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                            <option value="en">English</option>
                            <option value="it">Italiano</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Startup</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Auto-start on boot</p>
                            <p class="text-xs text-gray-500">Launch the app when your computer starts</p>
                        </div>
                        <input type="checkbox" wire:model="autoStart" class="w-5 h-5 accent-orange-500 rounded">
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Start minimized to tray</p>
                            <p class="text-xs text-gray-500">Don't show window on startup</p>
                        </div>
                        <input type="checkbox" wire:model="startMinimized" class="w-5 h-5 accent-orange-500 rounded">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Notifications</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Notification sound</p>
                        <p class="text-xs text-gray-500">Play a sound when alerts trigger</p>
                    </div>
                    <input type="checkbox" wire:model="notificationSound" class="w-5 h-5 accent-orange-500 rounded">
                </div>
                <div class="flex items-center justify-between mt-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Auto-download updates</p>
                        <p class="text-xs text-gray-500">Download new versions in the background</p>
                    </div>
                    <input type="checkbox" wire:model="autoDownloadUpdates" class="w-5 h-5 accent-orange-500 rounded">
                </div>
            </div>

            <button wire:click="saveGeneral" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors">Save General Settings</button>
        </div>
    @endif

    <!-- Providers Tab -->
    @if($activeTab === 'providers')
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Active Data Source</h3>
                <p class="text-sm text-gray-500 mb-4">Choose how the app receives sensor data from the collar.</p>
                <div class="space-y-3">
                    @foreach($providers as $provider)
                        <label class="flex items-start gap-3 p-4 border-2 {{ $activeProvider === $provider->getKey() ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }} rounded-xl cursor-pointer">
                            <input type="radio" wire:model="activeProvider" value="{{ $provider->getKey() }}" class="mt-1 accent-orange-500">
                            <div>
                                <span class="font-medium text-gray-800">{{ $provider->getName() }}</span>
                                @if($provider->isConfigured())
                                    <span class="ml-2 text-xs bg-teal-100 text-teal-600 px-2 py-0.5 rounded-full">Configured</span>
                                @else
                                    <span class="ml-2 text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Not configured</span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            @foreach($providers as $provider)
                @if(!empty($provider->getSettingsFields()))
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="font-bold text-gray-800 mb-4">{{ $provider->getName() }} Settings</h3>
                        <div class="space-y-4">
                            @foreach($provider->getSettingsFields() as $field)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field['label'] }}</label>
                                    <input
                                        type="{{ $field['type'] ?? 'text' }}"
                                        wire:model="providerSettings.{{ $provider->getKey() }}.{{ $field['key'] }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                                    >
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            <button wire:click="saveProviders" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors">Save Provider Settings</button>
        </div>
    @endif

    <!-- Thresholds Tab -->
    @if($activeTab === 'thresholds')
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-800 mb-1">Global Alert Thresholds</h3>
                <p class="text-sm text-gray-500 mb-6">These apply to all cats unless overridden per-cat.</p>

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

            <button wire:click="saveThresholds" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors">Save Thresholds</button>
        </div>
    @endif

    <!-- Cats Tab -->
    @if($activeTab === 'cats')
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Add a Cat</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input type="text" wire:model="newCatName" placeholder="Cat name" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    <select wire:model="newCatBreed" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none bg-white">
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
                    <button wire:click="addCat" class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors">+ Add Cat</button>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Your Cats ({{ $cats->count() }})</h3>
                <div class="space-y-2">
                    @forelse($cats as $cat)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🐱</span>
                                <div>
                                    <a href="{{ route('cat-detail', $cat) }}" class="font-medium text-gray-800 hover:text-orange-600">{{ $cat->name }}</a>
                                    <p class="text-xs text-gray-500">{{ $cat->breed }} · {{ $cat->birth_year ? 'Born ' . $cat->birth_year : 'Age unknown' }}</p>
                                </div>
                            </div>
                            <button wire:click="deleteCat({{ $cat->id }})" wire:confirm="Are you sure you want to delete {{ $cat->name }}?" class="text-red-500 hover:text-red-700 text-sm">🗑️ Delete</button>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 py-4 text-center">No cats yet. Add one above.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- About Tab -->
    @if($activeTab === 'about')
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="text-center mb-6">
                <div class="text-6xl mb-4">🐱</div>
                <h2 class="text-xl font-bold text-gray-800">Smart Cat Collar</h2>
                <p class="text-sm text-gray-500">Desktop Companion App v1.0.0</p>
            </div>
            <div class="space-y-2 text-sm text-gray-600">
                <p><span class="font-medium text-gray-700">Framework:</span> Laravel 12 + NativePHP (Electron)</p>
                <p><span class="font-medium text-gray-700">Database:</span> SQLite (offline-first)</p>
                <p><span class="font-medium text-gray-700">Frontend:</span> Blade + Livewire + Tailwind CSS</p>
                <p><span class="font-medium text-gray-700">Author:</span> Fabio (pacificDev)</p>
            </div>
            <div class="mt-6 bg-orange-50 rounded-xl p-4">
                <p class="text-sm text-orange-800">The desktop app is the central data hub. The ESP32 collar POSTs sensor data to the internal API. Communication channels are configurable above.</p>
            </div>
        </div>
    @endif

</div>
