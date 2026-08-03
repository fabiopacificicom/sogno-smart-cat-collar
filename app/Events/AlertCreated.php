<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when the AlertEngine creates an alert for a sensor reading.
 *
 * Listeners decide what to do with it — native desktop notification,
 * local push on mobile, logging, etc. Only `critical` alerts should
 * trigger user-facing notifications; `warning` alerts stay in-app.
 */
class AlertCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Alert $alert,
    ) {}

    /**
     * Whether this alert should trigger a user-facing notification.
     */
    public function shouldNotify(): bool
    {
        return $this->alert->type === 'critical';
    }
}
