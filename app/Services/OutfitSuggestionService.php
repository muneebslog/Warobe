<?php

namespace App\Services;

use App\Models\ClothingItem;
use App\Models\OutfitLog;
use App\Models\User;
use Illuminate\Support\Carbon;

class OutfitSuggestionService
{
    /**
     * Suggest the best outfit for the user (single best combination).
     *
     * @return array{shirt: ClothingItem|null, pant: ClothingItem|null, shalwar_kameez: ClothingItem|null}
     */
    public function suggestForUser(User $user, string $eventType): array
    {
        $combinations = $this->getTopCombinationsForUser($user, $eventType, 1);
        $best = $combinations[0] ?? null;

        if (! $best) {
            return ['shirt' => null, 'pant' => null, 'shalwar_kameez' => null];
        }

        return [
            'shirt' => $best['shirt'],
            'pant' => $best['pant'],
            'shalwar_kameez' => $best['shalwar_kameez'],
        ];
    }

    /**
     * Get top N combinations for the user (for AI or display).
     *
     * @return array<int, array{shirt: ClothingItem|null, pant: ClothingItem|null, shalwar_kameez: ClothingItem|null, score: float}>
     */
    public function getTopCombinationsForUser(User $user, string $eventType, int $limit = 3): array
    {
        $currentSeason = $this->currentSeason();
        $items = $user->clothingItems()->available()->get();

        $shalwarKameez = $items->where('type', 'shalwar_kameez');
        if ($shalwarKameez->isNotEmpty()) {
            $scored = $this->scoreShalwarKameezItems($shalwarKameez, $eventType, $currentSeason, $user);
            $sorted = $scored->sortByDesc('score')->values();
            $result = [];
            foreach ($sorted->take($limit) as $entry) {
                if ($this->isRecentlyWornCombination($user, null, null, $entry['item']->id)) {
                    continue;
                }
                $result[] = [
                    'shirt' => null,
                    'pant' => null,
                    'shalwar_kameez' => $entry['item'],
                    'score' => $entry['score'],
                ];
                if (count($result) >= $limit) {
                    break;
                }
            }
            return $result;
        }

        $shirts = $items->where('type', 'shirt');
        $pants = $items->where('type', 'pant');
        if ($shirts->isEmpty() || $pants->isEmpty()) {
            return [];
        }

        $scoredShirts = $this->scoreItems($shirts, $eventType, $currentSeason, $user)->sortByDesc('score')->values()->take(3);
        $scoredPants = $this->scoreItems($pants, $eventType, $currentSeason, $user)->sortByDesc('score')->values()->take(3);

        $combinations = [];
        foreach ($scoredShirts as $s) {
            foreach ($scoredPants as $p) {
                if ($this->isRecentlyWornCombination($user, $s['item']->id, $p['item']->id, null)) {
                    continue;
                }
                $comboScore = ($s['score'] + $p['score']) / 2;
                $comboScore += $this->scoreColorCompatibility($s['item']->color, $p['item']->color);
                $combinations[] = [
                    'shirt' => $s['item'],
                    'pant' => $p['item'],
                    'shalwar_kameez' => null,
                    'score' => $comboScore,
                ];
            }
        }
        usort($combinations, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($combinations, 0, $limit);
    }

    /**
     * Check if this exact combination was worn in the last rotation_days.
     */
    public function isRecentlyWornCombination(User $user, ?int $shirtId, ?int $pantId, ?int $shalwarKameezId): bool
    {
        $days = config('wardrobe.rotation_days', 5);
        $since = Carbon::now()->subDays($days);

        $query = OutfitLog::query()
            ->where('user_id', $user->id)
            ->where('worn_at', '>=', $since)
            ->where('shirt_id', $shirtId)
            ->where('pant_id', $pantId)
            ->where('shalwar_kameez_id', $shalwarKameezId);

        return $query->exists();
    }

    public function currentSeason(): string
    {
        $month = (int) Carbon::now()->format('n');
        if (in_array($month, [11, 12, 1, 2], true)) {
            return 'winter';
        }
        if (in_array($month, [5, 6, 7, 8], true)) {
            return 'summer';
        }
        return 'all';
    }

    /**
     * Score color compatibility (rule-based). Higher = better.
     */
    public function scoreColorCompatibility(string $color1, string $color2): float
    {
        $c1 = strtolower(trim($color1));
        $c2 = strtolower(trim($color2));
        $neutrals = config('wardrobe.neutral_colors', []);

        $n1 = in_array($c1, $neutrals, true);
        $n2 = in_array($c2, $neutrals, true);
        if ($n1 && $n2) {
            return 2.0; // both neutral
        }
        if ($n1 || $n2) {
            return 1.0; // one neutral matches anything
        }
        if ($c1 === $c2) {
            return -2.0; // same bright color
        }
        // Prefer contrast (simplified: different names = assume some contrast)
        return 0.5;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ClothingItem>  $items
     * @return \Illuminate\Support\Collection<int, array{item: ClothingItem, score: float}>
     */
    private function scoreItems($items, string $eventType, string $currentSeason, User $user)
    {
        $wearCounts = $this->preloadWearCounts($user->id, $items->pluck('id')->all());
        return $items->map(function (ClothingItem $item) use ($eventType, $currentSeason, $user, $wearCounts) {
            $score = $this->scoreItem($item, $eventType, $currentSeason, $user, $wearCounts);
            return ['item' => $item, 'score' => $score];
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ClothingItem>  $items
     * @return \Illuminate\Support\Collection<int, array{item: ClothingItem, score: float}>
     */
    private function scoreShalwarKameezItems($items, string $eventType, string $currentSeason, User $user)
    {
        return $this->scoreItems($items, $eventType, $currentSeason, $user);
    }

    /**
     * Preload wear counts for given clothing item IDs (shirt + pant + shalwar in one go).
     *
     * @param  array<int, int>  $itemIds
     * @return array<int, int>  clothing_item_id => total wear count
     */
    private function preloadWearCounts(int $userId, array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }
        $shirtCounts = OutfitLog::query()
            ->where('user_id', $userId)
            ->whereNotNull('shirt_id')
            ->whereIn('shirt_id', $itemIds)
            ->selectRaw('shirt_id as id, count(*) as c')
            ->groupBy('shirt_id')
            ->pluck('c', 'id')
            ->all();
        $pantCounts = OutfitLog::query()
            ->where('user_id', $userId)
            ->whereNotNull('pant_id')
            ->whereIn('pant_id', $itemIds)
            ->selectRaw('pant_id as id, count(*) as c')
            ->groupBy('pant_id')
            ->pluck('c', 'id')
            ->all();
        $skCounts = OutfitLog::query()
            ->where('user_id', $userId)
            ->whereNotNull('shalwar_kameez_id')
            ->whereIn('shalwar_kameez_id', $itemIds)
            ->selectRaw('shalwar_kameez_id as id, count(*) as c')
            ->groupBy('shalwar_kameez_id')
            ->pluck('c', 'id')
            ->all();
        $merged = [];
        foreach (array_unique(array_merge(array_keys($shirtCounts), array_keys($pantCounts), array_keys($skCounts))) as $id) {
            $merged[$id] = ($shirtCounts[$id] ?? 0) + ($pantCounts[$id] ?? 0) + ($skCounts[$id] ?? 0);
        }
        return $merged;
    }

    private function scoreItem(ClothingItem $item, string $eventType, string $currentSeason, User $user, array $wearCounts = []): float
    {
        $score = 0.0;

        if ($item->formality === $eventType) {
            $score += 5;
        }
        if ($item->season === $currentSeason) {
            $score += 3;
        } elseif ($item->season === 'all') {
            $score += 2;
        }

        $lastWorn = $item->last_worn_at;
        if ($lastWorn) {
            if ($lastWorn->isAfter(Carbon::now()->subDays(3))) {
                $score -= 5;
            } elseif ($lastWorn->isAfter(Carbon::now()->subDays(7))) {
                $score -= 3;
            }
        }

        $wearCount = $wearCounts[$item->id] ?? $item->wearCount();
        $score -= $wearCount * 0.5;

        return $score;
    }
}
