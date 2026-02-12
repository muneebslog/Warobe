<?php

namespace App\Livewire;

use App\Models\ClothingItem;
use App\Livewire\WardrobeDashboard;
use App\Services\ColorDetectionService;
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

    public string $color_family = 'grey';

    public ?string $color_hex = null;

    /** Shown when color detection fails (e.g. GD missing). */
    public ?string $color_detection_error = null;

    public string $formality = 'casual';

    public string $season = 'all';

    public $image = null;

    public function rules(): array
    {
        $families = implode(',', config('wardrobe.color_families', []));

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:shirt,pant,shalwar_kameez'],
            'color_family' => ['required', 'string', 'in:'.$families],
            'formality' => ['required', 'string', 'in:casual,office,wedding,jummah,eid,interview'],
            'season' => ['required', 'string', 'in:summer,winter,all'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:14096'],
        ];
    }

    public function updatedImage(): void
    {
        $this->color_detection_error = null;
        if (! $this->image) {
            $this->color_family = 'grey';
            $this->color_hex = null;
            return;
        }

        $mime = $this->image->getMimeType();
        $rasterMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
        if (! in_array($mime, $rasterMimes, true)) {
            return;
        }

        try {
            $contents = $this->image->get();
            if ($contents === null || $contents === '') {
                $this->color_detection_error = 'Could not read image data.';
                return;
            }

            $detected = app(ColorDetectionService::class)->detectDominantColorFromBinary($contents);
            $this->color_family = $detected['family'];
            $this->color_hex = $detected['hex'];
        } catch (\Throwable $e) {
            $this->color_family = 'grey';
            $this->color_hex = null;
            $this->color_detection_error = $e->getMessage();
        }
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
            'color' => $validated['color_family'],
            'color_family' => $validated['color_family'],
            'color_hex' => $this->color_hex,
            'formality' => $validated['formality'],
            'season' => $validated['season'],
            'status' => 'clean',
            'image_path' => $imagePath,
        ]);

        Cache::forget(WardrobeDashboard::dashboardStatsCacheKey());
        $this->reset(['name', 'color_family', 'color_hex', 'image', 'color_detection_error']);
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

    public static function colorFamilyOptions(): array
    {
        $families = config('wardrobe.color_families', []);
        return array_combine($families, array_map('ucfirst', $families));
    }

    public function render(): View
    {
        return view('livewire.add-clothing-item', [
            'typeOptions' => self::typeOptions(),
            'formalityOptions' => self::formalityOptions(),
            'seasonOptions' => self::seasonOptions(),
            'colorFamilyOptions' => self::colorFamilyOptions(),
        ]);
    }
}
