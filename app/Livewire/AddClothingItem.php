<?php

namespace App\Livewire;

use App\Models\ClothingItem;
use App\Livewire\WardrobeDashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class AddClothingItem extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $type = 'shirt';

    public string $color = '';

    public string $formality = 'casual';

    public string $season = 'all';

    public $image = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:shirt,pant,shalwar_kameez'],
            'color' => ['required', 'string', 'max:100'],
            'formality' => ['required', 'string', 'in:casual,office,wedding,jummah,eid,interview'],
            'season' => ['required', 'string', 'in:summer,winter,all'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('clothing', 'public');
        }

        ClothingItem::query()->create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'color' => $validated['color'],
            'formality' => $validated['formality'],
            'season' => $validated['season'],
            'status' => 'clean',
            'image_path' => $imagePath,
        ]);

        Cache::forget(WardrobeDashboard::dashboardStatsCacheKey());
        $this->reset(['name', 'color', 'image']);
        $this->resetValidation();
        $this->dispatch('refresh-wardrobe');

        $this->redirect(route('wardrobe'), navigate: true);
    }

    public static function typeOptions(): array
    {
        return [
            'shirt' => 'Shirt',
            'pant' => 'Pant',
            'shalwar_kameez' => 'Shalwar Kameez',
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

    public static function seasonOptions(): array
    {
        return [
            'summer' => 'Summer',
            'winter' => 'Winter',
            'all' => 'All',
        ];
    }

    public function render(): View
    {
        return view('livewire.add-clothing-item', [
            'typeOptions' => self::typeOptions(),
            'formalityOptions' => self::formalityOptions(),
            'seasonOptions' => self::seasonOptions(),
        ]);
    }
}
