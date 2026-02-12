<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutfitLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'event_type',
        'shirt_id',
        'pant_id',
        'shalwar_kameez_id',
        'worn_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'worn_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the outfit log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shirt clothing item.
     */
    public function shirt(): BelongsTo
    {
        return $this->belongsTo(ClothingItem::class, 'shirt_id');
    }

    /**
     * Get the pant clothing item.
     */
    public function pant(): BelongsTo
    {
        return $this->belongsTo(ClothingItem::class, 'pant_id');
    }

    /**
     * Get the shalwar kameez clothing item.
     */
    public function shalwarKameez(): BelongsTo
    {
        return $this->belongsTo(ClothingItem::class, 'shalwar_kameez_id');
    }
}
