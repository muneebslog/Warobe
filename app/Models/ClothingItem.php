<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClothingItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'color',
        'formality',
        'season',
        'status',
        'return_date',
        'image_path',
        'last_worn_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'last_worn_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the clothing item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to items with status 'clean'.
     */
    public function scopeClean(Builder $query): Builder
    {
        return $query->where('status', 'clean');
    }

    /**
     * Scope to items that are available (status = clean).
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'clean');
    }

    /**
     * Get outfit logs where this item was the shirt.
     */
    public function shirtOutfitLogs(): HasMany
    {
        return $this->hasMany(OutfitLog::class, 'shirt_id');
    }

    /**
     * Get outfit logs where this item was the pant.
     */
    public function pantOutfitLogs(): HasMany
    {
        return $this->hasMany(OutfitLog::class, 'pant_id');
    }

    /**
     * Get outfit logs where this item was the shalwar kameez.
     */
    public function shalwarKameezOutfitLogs(): HasMany
    {
        return $this->hasMany(OutfitLog::class, 'shalwar_kameez_id');
    }

    /**
     * Get all dry clean logs for this item.
     */
    public function dryCleanLogs(): HasMany
    {
        return $this->hasMany(DryCleanLog::class);
    }

    /**
     * Send the clothing item to dry clean.
     *
     * @param  \DateTimeInterface|null  $expectedReturnDate
     */
    public function sendToDryClean(?\DateTimeInterface $expectedReturnDate = null, ?float $cost = null, ?string $notes = null): DryCleanLog
    {
        return DB::transaction(function () use ($expectedReturnDate, $cost, $notes) {
            $log = DryCleanLog::create([
                'user_id' => $this->user_id,
                'clothing_item_id' => $this->id,
                'sent_at' => now(),
                'expected_return_date' => $expectedReturnDate,
                'cost' => $cost,
                'notes' => $notes,
            ]);

            $this->update([
                'status' => 'dry_clean',
                'return_date' => $expectedReturnDate,
            ]);

            return $log;
        });
    }

    /**
     * Mark the latest dry clean as received.
     */
    public function markAsReceived(): void
    {
        DB::transaction(function () {
            $log = $this->dryCleanLogs()
                ->whereNull('received_at')
                ->latest('sent_at')
                ->first();

            if ($log instanceof DryCleanLog) {
                $log->update(['received_at' => now()]);
            }

            $this->update([
                'status' => 'clean',
                'return_date' => null,
            ]);
        });
    }

    /**
     * Check if the item is overdue from dry clean.
     */
    public function isOverdue(): bool
    {
        if ($this->status !== 'dry_clean') {
            return false;
        }

        $latestLog = $this->dryCleanLogs()
            ->whereNull('received_at')
            ->latest('sent_at')
            ->first();

        if (! $latestLog || $latestLog->expected_return_date === null) {
            return false;
        }

        return $latestLog->expected_return_date->isPast();
    }

    /**
     * Total number of times this item appeared in outfit logs (any role).
     */
    public function wearCount(): int
    {
        $shirt = $this->shirtOutfitLogs()->count();
        $pant = $this->pantOutfitLogs()->count();
        $sk = $this->shalwarKameezOutfitLogs()->count();

        return $shirt + $pant + $sk;
    }
}
