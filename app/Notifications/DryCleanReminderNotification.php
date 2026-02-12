<?php

namespace App\Notifications;

use App\Models\ClothingItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DryCleanReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ClothingItem $clothingItem,
        public bool $isOverdue
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
            'type' => 'dry_clean_reminder',
            'clothing_item_id' => $this->clothingItem->id,
            'clothing_item_name' => $this->clothingItem->name,
            'is_overdue' => $this->isOverdue,
            'expected_return_date' => $this->clothingItem->return_date?->toDateString(),
        ];
    }
}
