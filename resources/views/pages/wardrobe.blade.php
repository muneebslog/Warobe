<x-layouts::app :title="__('Wardrobe')">
    <div class="space-y-6 p-4 md:p-6">
        <div>
            <flux:heading>{{ __('Wardrobe') }}</flux:heading>
            <flux:subheading>{{ __('Your clothing items') }}</flux:subheading>
        </div>

        <livewire:wardrobe-grid lazy />
    </div>
</x-layouts::app>
