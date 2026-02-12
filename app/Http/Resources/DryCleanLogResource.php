<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DryCleanLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'clothing_item_id' => $this->clothing_item_id,
            'sent_at' => $this->sent_at->toIso8601String(),
            'expected_return_date' => $this->expected_return_date?->toDateString(),
            'received_at' => $this->received_at?->toIso8601String(),
            'cost' => $this->cost !== null ? (float) $this->cost : null,
            'notes' => $this->notes,
            'clothing_item' => $this->whenLoaded('clothingItem', fn () => new ClothingItemResource($this->clothingItem)),
        ];
    }
}
