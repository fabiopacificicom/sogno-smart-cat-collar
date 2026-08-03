<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-2xl border border-gray-200 p-8">
        <div class="text-center mb-6">
            <span class="text-5xl">🐱</span>
            <h1 class="text-xl font-bold text-gray-800 mt-3">Pair with your Desktop</h1>
            <p class="text-sm text-gray-500 mt-2">
                Link this phone to your Smart Cat Collar desktop app to see your cats' live data.
            </p>
        </div>

        @if($error)
            <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg px-4 py-3 mb-4">
                {{ $error }}
            </div>
        @endif

        <form wire:submit="pair" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desktop address</label>
                <input type="text" wire:model="host" placeholder="192.168.1.113:8001"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-base">
                <p class="text-xs text-gray-400 mt-1">Shown in desktop Settings → Mobile Devices. Same WiFi required.</p>
                @error('host') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pairing code</label>
                <input type="text" wire:model="code" placeholder="000000" inputmode="numeric" maxlength="6"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-center text-2xl font-mono tracking-[0.4em]">
                @error('code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled"
                class="w-full px-4 py-3.5 bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white font-medium rounded-lg transition-colors text-base">
                <span wire:loading.remove wire:target="pair">🔗 Pair &amp; Sync</span>
                <span wire:loading wire:target="pair">Pairing &amp; syncing…</span>
            </button>
        </form>

        <p class="text-xs text-gray-400 text-center mt-6">
            On your desktop: Settings → Mobile Devices → Generate Pairing Code
        </p>
    </div>
</div>
