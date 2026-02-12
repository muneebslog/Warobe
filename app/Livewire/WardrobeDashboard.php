<?php

namespace App\Livewire;

use App\Models\ClothingItem;
use App\Models\DryCleanLog;
use App\Models\OutfitLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class WardrobeDashboard extends Component
{
    public static function dashboardStatsCacheKey(?int $userId = null): string
    {
        return 'dashboard_stats_user_' . ($userId ?? auth()->id());
    }

    public function totalItems(): int
    {
        return ClothingItem::query()
            ->where('user_id', auth()->id())
            ->count();
    }

    public function cleanCount(): int
    {
        return ClothingItem::query()
            ->where('user_id', auth()->id())
            ->where('status', 'clean')
            ->count();
    }

    public function dryCleanCount(): int
    {
        return ClothingItem::query()
            ->where('user_id', auth()->id())
            ->where('status', 'dry_clean')
            ->count();
    }

    public function laundryCount(): int
    {
        return ClothingItem::query()
            ->where('user_id', auth()->id())
            ->where('status', 'laundry')
            ->count();
    }

    public function totalDryCleanCost(): string
    {
        $total = DryCleanLog::query()
            ->where('user_id', auth()->id())
            ->sum('cost');

        return number_format((float) $total, 2);
    }

    public function mostWornItem(): ?ClothingItem
    {
        $ids = OutfitLog::query()
            ->where('user_id', auth()->id())
            ->get()
            ->flatMap(fn (OutfitLog $log) => array_filter([
                $log->shirt_id,
                $log->pant_id,
                $log->shalwar_kameez_id,
            ]));

        if ($ids->isEmpty()) {
            return null;
        }

        $counts = $ids->countBy();
        $topId = $counts->sortDesc()->keys()->first();

        return ClothingItem::query()
            ->where('user_id', auth()->id())
            ->find($topId);
    }

    public function lastWornOutfit(): ?OutfitLog
    {
        return OutfitLog::query()
            ->where('user_id', auth()->id())
            ->with(['shirt', 'pant', 'shalwarKameez'])
            ->latest('worn_at')
            ->first();
    }

    public function placeholder(): View
    {
        return view('livewire.placeholders.wardrobe-dashboard');
    }

    public function render(): View
    {
        $stats = Cache::remember(
            self::dashboardStatsCacheKey(),
            now()->addMinutes(5),
            fn () => [
                'totalItems' => $this->totalItems(),
                'cleanCount' => $this->cleanCount(),
                'dryCleanCount' => $this->dryCleanCount(),
                'laundryCount' => $this->laundryCount(),
                'totalDryCleanCost' => $this->totalDryCleanCost(),
                'mostWornItem' => $this->mostWornItem(),
                'lastWornOutfit' => $this->lastWornOutfit(),
            ]
        );

        return view('livewire.wardrobe-dashboard', $stats);
    }
}
