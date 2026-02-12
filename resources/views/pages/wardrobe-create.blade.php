<x-layouts::app :title="__('Add clothing')">
    <div class="p-4 md:p-6">
        <div class="mx-auto max-w-2xl">
            <div class="mb-6">
                <flux:heading>{{ __('Add clothing') }}</flux:heading>
                <flux:subheading>{{ __('Add a new item to your wardrobe') }}</flux:subheading>
            </div>

            <livewire:add-clothing-item />
        </div>
    </div>
</x-layouts::app>
