<?php

namespace App\Livewire;

use App\Models\ClothingItem;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class WardrobeGrid extends Component
{
    use WithPagination;

    public string $filterType = '';

    public string $filterStatus = '';

    public string $filterFormality = '';

    public bool $showDryCleanModal = false;

    public ?int $dryCleanItemId = null;

    public ?string $dryCleanExpectedReturnDate = null;

    public ?string $dryCleanCost = null;

    public ?string $dryCleanNotes = null;

    protected $listeners = ['refresh-wardrobe' => '$refresh'];

    public function getClothingItemsProperty(): Paginator
    {
        $query = ClothingItem::query()
            ->where('user_id', auth()->id())
            ->orderBy('name');

        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }
        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }
        if ($this->filterFormality !== '') {
            $query->where('formality', $this->filterFormality);
        }

        return $query->simplePaginate(12);
    }

    public function markAsWorn(int $id): void
    {
        $item = ClothingItem::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $item->update([
            'status' => 'worn',
            'last_worn_at' => now(),
        ]);

        Cache::forget(WardrobeDashboard::dashboardStatsCacheKey());
        $this->dispatch('refresh-wardrobe');
    }

    public function openDryCleanModal(int $id): void
    {
        $this->dryCleanItemId = $id;
        $this->dryCleanExpectedReturnDate = '';
        $this->dryCleanCost = '';
        $this->dryCleanNotes = '';
        $this->showDryCleanModal = true;
        $this->resetValidation();
    }

    public function closeDryCleanModal(): void
    {
        $this->showDryCleanModal = false;
        $this->dryCleanItemId = null;
        $this->dryCleanExpectedReturnDate = null;
        $this->dryCleanCost = null;
        $this->dryCleanNotes = null;
    }

    public function sendToDryClean(): void
    {
        $this->validate([
            'dryCleanExpectedReturnDate' => 'nullable|date',
            'dryCleanCost' => 'nullable|numeric|min:0',
            'dryCleanNotes' => 'nullable|string|max:1000',
        ]);

        $item = ClothingItem::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->dryCleanItemId);

        $expectedDate = $this->dryCleanExpectedReturnDate
            ? \Carbon\Carbon::parse($this->dryCleanExpectedReturnDate)
            : null;
        $cost = $this->dryCleanCost !== null && $this->dryCleanCost !== ''
            ? (float) $this->dryCleanCost
            : null;

        $item->sendToDryClean($expectedDate, $cost, $this->dryCleanNotes ?: null);

        Cache::forget(WardrobeDashboard::dashboardStatsCacheKey());
        $this->closeDryCleanModal();
        $this->dispatch('refresh-wardrobe');
    }

    public function markAsReceived(int $id): void
    {
        $item = ClothingItem::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $item->markAsReceived();

        Cache::forget(WardrobeDashboard::dashboardStatsCacheKey());
        $this->dispatch('refresh-wardrobe');
    }

    public static function typeOptions(): array
    {
        return [
            'shirt' => 'Shirt',
            'pant' => 'Pant',
            'shalwar_kameez' => 'Shalwar Kameez',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'clean' => 'Clean',
            'worn' => 'Worn',
            'dry_clean' => 'Dry Clean',
            'laundry' => 'Laundry',
        ];
    }

    public static function formalityOptions(): array
    {
        return [
            'casual' => 'Casual',
            'office' => 'Office',
            'wedding' => 'Wedding',
            'jummah' => 'Jummah',
            'eid' => 'Eid',
            'interview' => 'Interview',
        ];
    }

    public static function statusBadgeColor(string $status): string
    {
        return match ($status) {
            'clean' => 'green',
            'worn' => 'zinc',
            'dry_clean' => 'amber',
            'laundry' => 'blue',
            default => 'zinc',
        };
    }

    public function placeholder(): View
    {
        return view('livewire.placeholders.wardrobe-grid');
    }

    public function render(): View
    {
        return view('livewire.wardrobe-grid', [
            'clothingItems' => $this->clothingItems,
            'typeOptions' => self::typeOptions(),
            'statusOptions' => self::statusOptions(),
            'formalityOptions' => self::formalityOptions(),
        ]);
    }
}
