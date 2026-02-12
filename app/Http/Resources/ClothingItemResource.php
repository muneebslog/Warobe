<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClothingItemResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'color' => $this->color,
            'formality' => $this->formality,
            'season' => $this->season,
            'status' => $this->status,
            'return_date' => $this->return_date?->toDateString(),
            'image_path' => $this->image_path,
            'image_url' => $this->image_path ? url('storage/'.$this->image_path) : null,
            'last_worn_at' => $this->last_worn_at?->toIso8601String(),
            'is_overdue' => $this->resource->isOverdue(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
