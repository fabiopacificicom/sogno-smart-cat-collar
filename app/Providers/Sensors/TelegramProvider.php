<?php

namespace App\Providers\Sensors;

use App\Models\ProviderSetting;
use Illuminate\Support\Facades\Http;

/**
 * Polls the Telegram Bot API for collar messages.
 *
 * Used as a fallback channel when the direct API is unavailable.
 * The collar sends messages to a Telegram bot; this provider polls for them.
 */
class TelegramProvider implements SensorDataProvider
{
    public function getName(): string
    {
        return '✈️ Telegram Bot';
    }

    public function getKey(): string
    {
        return 'telegram';
    }

    public function isConfigured(): bool
    {
        return ! empty(ProviderSetting::get('telegram', 'bot_token'));
    }

    public function fetchData(): ?SensorData
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $token = ProviderSetting::get('telegram', 'bot_token');
        $offset = (int) ProviderSetting::get('telegram', 'last_update_id', '0');

        try {
            $response = Http::timeout(5)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                'offset' => $offset + 1,
                'limit' => 1,
                'timeout' => 3,
            ]);

            if (! $response->ok()) {
                return null;
            }

            $data = $response->json();
            $updates = $data['result'] ?? [];

            if (empty($updates)) {
                return null;
            }

            $update = $updates[0];
            ProviderSetting::set('telegram', 'last_update_id', (string) $update['update_id']);

            $text = $update['message']['text'] ?? '';
            return $this->parseCollarMessage($text);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getSettingsFields(): array
    {
        return [
            [
                'key' => 'bot_token',
                'label' => 'Telegram Bot Token',
                'type' => 'password',
                'placeholder' => '123456:ABC-DEF...',
            ],
            [
                'key' => 'polling_interval',
                'label' => 'Polling interval (seconds)',
                'type' => 'text',
                'placeholder' => '15',
            ],
        ];
    }

    /**
     * Parse a collar message in the format:
     * "CAT:1 T:38.5 BPM:180 ACT:medium"
     */
    private function parseCollarMessage(string $text): ?SensorData
    {
        if (! preg_match('/CAT:(\d+)\s+T:([\d.]+)\s+BPM:(\d+)\s+ACT:(\w+)/i', $text, $m)) {
            return null;
        }

        return new SensorData(
            catId: (int) $m[1],
            temperature: (float) $m[2],
            bpm: (int) $m[3],
            activity: strtolower($m[4]),
            source: 'telegram',
            deviceId: 'esp32s3-telegram',
            readAt: now(),
        );
    }
}
