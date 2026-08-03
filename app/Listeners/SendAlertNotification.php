<?php

namespace App\Listeners;

use App\Events\AlertCreated;
use Native\Laravel\Facades\Notification;

/**
 * Sends a user-facing notification when a critical alert is raised.
 *
 * Desktop (NativePHP Electron): native OS notification via the
 * Native\Laravel Notification facade.
 *
 * Mobile (NativePHP Mobile): local push notification via the
 * nativephp/mobile-local-notifications plugin. That plugin is a paid
 * add-on and may not be installed yet, so we detect it by class and
 * skip gracefully when absent — the alert is still persisted and the
 * desktop notification still fires.
 *
 * Only `critical` alerts reach this listener's notification path;
 * `warning` alerts are deliberately left as in-app only.
 */
class SendAlertNotification
{
    public function handle(AlertCreated $event): void
    {
        if (! $event->shouldNotify()) {
            return;
        }

        $alert = $event->alert;
        $catName = $alert->cat?->name ?? 'Your cat';
        $title = "🚨 {$catName} — critical alert";
        $body = $alert->message;

        $this->notifyDesktop($title, $body);
        $this->notifyMobile($title, $body, $alert);
    }

    /**
     * Native desktop notification (Electron). No-op outside the desktop shell.
     */
    protected function notifyDesktop(string $title, string $body): void
    {
        try {
            Notification::title($title)->message($body)->show();
        } catch (\Throwable $e) {
            // Not running inside the Electron shell (e.g. plain `artisan serve`,
            // tests, or the mobile runtime) — skip silently.
            report($e);
        }
    }

    /**
     * Local push on mobile via the premium local-notifications plugin.
     * Detected by class so this listener works whether or not the plugin
     * is installed yet.
     */
    protected function notifyMobile(string $title, string $body, $alert): void
    {
        $facade = 'NativePHP\\LocalNotifications\\Facades\\LocalNotifications';

        if (! class_exists($facade)) {
            return; // plugin not installed
        }

        try {
            $facade::send('alert-'.$alert->id)
                ->title($title)
                ->body($body)
                ->data([
                    'alert_id' => $alert->id,
                    'cat_id' => $alert->cat_id,
                    'vital' => $alert->vital,
                ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
