<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Models\ClothingItem;
use App\Models\DryCleanLog;
use App\Models\OutfitLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalItems = ClothingItem::query()->where('user_id', $user->id)->count();
        $cleanCount = ClothingItem::query()->where('user_id', $user->id)->where('status', 'clean')->count();
        $dryCleanCount = ClothingItem::query()->where('user_id', $user->id)->where('status', 'dry_clean')->count();
        $laundryCount = ClothingItem::query()->where('user_id', $user->id)->where('status', 'laundry')->count();
        $totalDryCleanCost = (string) DryCleanLog::query()->where('user_id', $user->id)->sum('cost');

        $mostWornItem = $this->mostWornItem($user->id);
        $lastWornOutfit = OutfitLog::query()
            ->where('user_id', $user->id)
            ->with(['shirt', 'pant', 'shalwarKameez'])
            ->latest('worn_at')
            ->first();

        $data = [
            'total_items' => $totalItems,
            'clean_count' => $cleanCount,
            'dry_clean_count' => $dryCleanCount,
            'laundry_count' => $laundryCount,
            'total_dry_clean_cost' => $totalDryCleanCost,
            'most_worn_item' => $mostWornItem,
            'last_worn_outfit' => $lastWornOutfit,
        ];

        return new JsonResponse(new DashboardResource($data));
    }

    private function mostWornItem(int $userId): ?ClothingItem
    {
        $ids = OutfitLog::query()
            ->where('user_id', $userId)
            ->get()
            ->flatMap(fn (OutfitLog $log) => array_filter([
                $log->shirt_id,
                $log->pant_id,
                $log->shalwar_kameez_id,
            ]));

        if ($ids->isEmpty()) {
            return null;
        }

        $topId = $ids->countBy()->sortDesc()->keys()->first();

        return ClothingItem::query()->where('user_id', $userId)->find($topId);
    }
}
