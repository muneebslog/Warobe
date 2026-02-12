<?php

namespace App\Console\Commands;

use App\Models\ClothingItem;
use App\Models\DryCleanLog;
use App\Models\User;
use App\Notifications\DryCleanReminderNotification;
use App\Notifications\UnusedClothesReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WardrobeCheckRemindersCommand extends Command
{
    protected $signature = 'wardrobe:check-reminders';

    protected $description = 'Check dry clean and unused clothes reminders and send database notifications';

    public function handle(): int
    {
        $this->checkDryCleanReminders();
        $this->checkUnusedClothesReminders();

        return self::SUCCESS;
    }

    private function checkDryCleanReminders(): void
    {
        $items = ClothingItem::query()
            ->where('status', 'dry_clean')
            ->with('user')
            ->get();

        foreach ($items as $item) {
            $latestLog = $item->dryCleanLogs()->whereNull('received_at')->latest('sent_at')->first();
            if (! $latestLog || $latestLog->expected_return_date === null) {
                continue;
            }
            $expected = $latestLog->expected_return_date;
            if ($expected->isToday() || $expected->isPast()) {
                $item->user->notify(new DryCleanReminderNotification($item, $expected->isPast()));
            }
        }
    }

    private function checkUnusedClothesReminders(): void
    {
        $thresholdDays = config('wardrobe.unused_days_threshold', 30);
        $since = Carbon::now()->subDays($thresholdDays);

        $cacheKey = 'wardrobe_unused_reminder_last_sent';
        $lastSent = \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
        if (Carbon::createFromTimestamp($lastSent)->diffInDays(Carbon::now()) < 7) {
            return;
        }

        $userIds = ClothingItem::query()->distinct()->pluck('user_id');
        foreach ($userIds as $userId) {
            $user = User::query()->find($userId);
            if (! $user) {
                continue;
            }
            $unused = ClothingItem::query()
                ->where('user_id', $userId)
                ->where(function ($q) use ($since) {
                    $q->whereNull('last_worn_at')->orWhere('last_worn_at', '<', $since);
                })
                ->get();
            if ($unused->isEmpty()) {
                continue;
            }
            $user->notify(new UnusedClothesReminderNotification(
                $unused->map(fn ($i) => ['id' => $i->id, 'name' => $i->name])->values()->all()
            ));
        }

        \Illuminate\Support\Facades\Cache::put($cacheKey, Carbon::now()->timestamp, now()->addDays(8));
    }
}
