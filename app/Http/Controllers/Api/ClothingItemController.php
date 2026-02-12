<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreClothingItemRequest;
use App\Http\Requests\Api\UpdateClothingItemRequest;
use App\Http\Resources\ClothingItemResource;
use App\Models\ClothingItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClothingItemController extends Controller
{
    /**
     * Display a listing of the user's clothing items.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->clothingItems()->orderBy('name');

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('formality')) {
            $query->where('formality', $request->input('formality'));
        }

        $items = $query->paginate($request->input('per_page', 15));

        return ClothingItemResource::collection($items);
    }

    /**
     * Store a newly created clothing item.
     */
    public function store(StoreClothingItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('clothing', 'public');
        }

        $item = $request->user()->clothingItems()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'color' => $validated['color'],
            'formality' => $validated['formality'],
            'season' => $validated['season'],
            'status' => 'clean',
            'image_path' => $imagePath,
        ]);

        return (new ClothingItemResource($item))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified clothing item.
     */
    public function show(Request $request, int $clothing_item): JsonResponse
    {
        $item = $request->user()->clothingItems()->findOrFail($clothing_item);

        return new JsonResponse(new ClothingItemResource($item));
    }

    /**
     * Update the specified clothing item.
     */
    public function update(UpdateClothingItemRequest $request, int $clothing_item): JsonResponse
    {
        $item = $request->user()->clothingItems()->findOrFail($clothing_item);
        $validated = $request->validated();

        $data = [];
        foreach (['name', 'type', 'color', 'formality', 'season', 'status'] as $key) {
            if (array_key_exists($key, $validated)) {
                $data[$key] = $validated[$key];
            }
        }
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('clothing', 'public');
        }
        $item->update($data);

        return new JsonResponse(new ClothingItemResource($item->fresh()));
    }

    /**
     * Remove the specified clothing item.
     */
    public function destroy(Request $request, int $clothing_item): JsonResponse
    {
        $item = $request->user()->clothingItems()->findOrFail($clothing_item);
        $item->delete();

        return new JsonResponse(null, 204);
    }
}
