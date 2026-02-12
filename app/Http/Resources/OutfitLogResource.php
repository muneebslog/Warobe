<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutfitLogResource extends JsonResource
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
            'event_type' => $this->event_type,
            'shirt_id' => $this->shirt_id,
            'pant_id' => $this->pant_id,
            'shalwar_kameez_id' => $this->shalwar_kameez_id,
            'worn_at' => $this->worn_at->toIso8601String(),
            'shirt' => $this->whenLoaded('shirt', fn () => new ClothingItemResource($this->shirt)),
            'pant' => $this->whenLoaded('pant', fn () => new ClothingItemResource($this->pant)),
            'shalwar_kameez' => $this->whenLoaded('shalwarKameez', fn () => new ClothingItemResource($this->shalwarKameez)),
        ];
    }
}
