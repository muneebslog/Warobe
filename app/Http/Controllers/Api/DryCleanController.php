<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendToDryCleanRequest;
use App\Http\Resources\ClothingItemResource;
use App\Http\Resources\DryCleanLogResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DryCleanController extends Controller
{
    /**
     * Send a clothing item to dry clean.
     */
    public function sendToDryClean(SendToDryCleanRequest $request, int $clothing_item): JsonResponse
    {
        $item = $request->user()->clothingItems()->findOrFail($clothing_item);
        $validated = $request->validated();

        $expectedReturnDate = isset($validated['expected_return_date'])
            ? new \DateTimeImmutable($validated['expected_return_date'])
            : null;
        $cost = isset($validated['cost']) ? (float) $validated['cost'] : null;
        $notes = $validated['notes'] ?? null;

        $log = $item->sendToDryClean($expectedReturnDate, $cost, $notes);

        return (new JsonResponse([
            'message' => 'Sent to dry clean.',
            'dry_clean_log' => new DryCleanLogResource($log),
            'clothing_item' => new ClothingItemResource($item->fresh()),
        ]))->setStatusCode(200);
    }

    /**
     * Mark the clothing item as received from dry clean.
     */
    public function markReceived(Request $request, int $clothing_item): JsonResponse
    {
        $item = $request->user()->clothingItems()->findOrFail($clothing_item);
        $item->markAsReceived();

        return new JsonResponse([
            'message' => 'Marked as received.',
            'clothing_item' => new ClothingItemResource($item->fresh()),
        ]);
    }

    /**
     * List the user's dry clean logs.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $logs = $request->user()
            ->dryCleanLogs()
            ->with('clothingItem')
            ->latest('sent_at')
            ->paginate($request->input('per_page', 15));

        return DryCleanLogResource::collection($logs);
    }
}
