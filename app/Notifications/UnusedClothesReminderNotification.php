<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UnusedClothesReminderNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, array{id: int, name: string}>  $items
     */
    public function __construct(
        public array $items
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'unused_clothes_reminder',
            'items' => $this->items,
            'count' => count($this->items),
        ];
    }
}
