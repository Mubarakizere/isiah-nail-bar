<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ServiceStatusNotification extends Notification
{
    use Queueable;

    public $serviceName;
    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($serviceName, $status)
    {
        $this->serviceName = $serviceName;
        $this->status = $status;
    }

    /**
     * Get the notification delivery channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Store the notification in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => "Your service '{$this->serviceName}' has been {$this->status}."
        ];
    }
}
