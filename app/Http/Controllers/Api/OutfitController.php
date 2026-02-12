<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SuggestOutfitRequest;
use App\Http\Requests\Api\WearOutfitRequest;
use App\Http\Resources\ClothingItemResource;
use App\Http\Resources\OutfitLogResource;
use App\Models\ClothingItem;
use App\Models\OutfitLog;
use App\Services\OutfitSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OutfitController extends Controller
{
    /**
     * Suggest an outfit for the given event type.
     */
    public function suggest(SuggestOutfitRequest $request): JsonResponse
    {
        $user = $request->user();
        $service = app(OutfitSuggestionService::class);
        $suggestion = $service->suggestForUser($user, $request->validated('event_type'));

        return new JsonResponse([
            'shirt' => $suggestion['shirt'] ? new ClothingItemResource($suggestion['shirt']) : null,
            'pant' => $suggestion['pant'] ? new ClothingItemResource($suggestion['pant']) : null,
            'shalwar_kameez' => $suggestion['shalwar_kameez']
                ? new ClothingItemResource($suggestion['shalwar_kameez'])
                : null,
        ]);
    }

    /**
     * Record wearing an outfit and update item status.
     */
    public function wear(WearOutfitRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $log = OutfitLog::query()->create([
            'user_id' => $user->id,
            'event_type' => $validated['event_type'],
            'shirt_id' => $validated['shirt_id'] ?? null,
            'pant_id' => $validated['pant_id'] ?? null,
            'shalwar_kameez_id' => $validated['shalwar_kameez_id'] ?? null,
            'worn_at' => now(),
        ]);

        $ids = array_filter([
            $validated['shirt_id'] ?? null,
            $validated['pant_id'] ?? null,
            $validated['shalwar_kameez_id'] ?? null,
        ]);

        if ($ids !== []) {
            ClothingItem::query()
                ->where('user_id', $user->id)
                ->whereIn('id', $ids)
                ->update([
                    'status' => 'worn',
                    'last_worn_at' => now(),
                ]);
        }

        return (new JsonResponse([
            'message' => 'Outfit recorded.',
            'outfit_log' => new OutfitLogResource($log->load(['shirt', 'pant', 'shalwarKameez'])),
        ]))->setStatusCode(201);
    }

    /**
     * List the user's outfit logs.
     */
    public function logs(Request $request): AnonymousResourceCollection
    {
        $logs = $request->user()
            ->outfitLogs()
            ->with(['shirt', 'pant', 'shalwarKameez'])
            ->latest('worn_at')
            ->paginate($request->input('per_page', 15));

        return OutfitLogResource::collection($logs);
    }
}
