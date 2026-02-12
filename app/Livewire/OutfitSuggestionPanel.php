<?php

namespace App\Livewire;

use App\Models\ClothingItem;
use App\Models\OutfitLog;
use App\Services\AiStylistService;
use App\Services\OutfitSuggestionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class OutfitSuggestionPanel extends Component
{
    public string $eventType = 'office';

    public ?array $suggestion = null;

    public ?string $explanation = null;

    public function suggest(): void
    {
        $user = auth()->user();
        $useAi = config('wardrobe.enable_ai', false);
        $service = $useAi ? app(AiStylistService::class) : app(OutfitSuggestionService::class);
        $result = $service->suggestForUser($user, $this->eventType);

        $this->suggestion = [
            'shirt' => $result['shirt'] ?? null,
            'pant' => $result['pant'] ?? null,
            'shalwar_kameez' => $result['shalwar_kameez'] ?? null,
        ];
        $this->explanation = $result['explanation'] ?? null;
    }

    public function wearThisOutfit(): void
    {
        if (! $this->suggestion) {
            return;
        }

        $shirt = $this->suggestion['shirt'];
        $pant = $this->suggestion['pant'];
        $shalwarKameez = $this->suggestion['shalwar_kameez'];

        $user = auth()->user();

        OutfitLog::query()->create([
            'user_id' => $user->id,
            'event_type' => $this->eventType,
            'shirt_id' => $shirt?->id,
            'pant_id' => $pant?->id,
            'shalwar_kameez_id' => $shalwarKameez?->id,
            'worn_at' => now(),
        ]);

        $ids = collect([$shirt?->id, $pant?->id, $shalwarKameez?->id])->filter()->values()->all();
        ClothingItem::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $ids)
            ->update([
                'status' => 'worn',
                'last_worn_at' => now(),
            ]);

        Cache::forget(WardrobeDashboard::dashboardStatsCacheKey());
        $this->suggestion = null;
        $this->dispatch('refresh-wardrobe');
    }

    public static function eventTypeOptions(): array
    {
        return [
            'casual' => 'Casual',
            'office' => 'Office',
            'wedding' => 'Wedding',
            'jummah' => 'Jummah',
            'eid' => 'Eid',
            'interview' => 'Interview',
        ];
    }

    public function placeholder(): View
    {
        return view('livewire.placeholders.outfit-suggestion-panel');
    }

    public function render(): View
    {
        return view('livewire.outfit-suggestion-panel', [
            'eventTypeOptions' => self::eventTypeOptions(),
        ]);
    }
}
