<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'total_items' => $data['total_items'],
            'clean_count' => $data['clean_count'],
            'dry_clean_count' => $data['dry_clean_count'],
            'laundry_count' => $data['laundry_count'],
            'total_dry_clean_cost' => (float) $data['total_dry_clean_cost'],
            'most_worn_item' => isset($data['most_worn_item'])
                ? (new ClothingItemResource($data['most_worn_item']))->toArray($request)
                : null,
            'last_worn_outfit' => isset($data['last_worn_outfit'])
                ? (new OutfitLogResource($data['last_worn_outfit']))->toArray($request)
                : null,
        ];
    }
}
