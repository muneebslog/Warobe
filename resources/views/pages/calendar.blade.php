<x-layouts::app :title="__('Calendar')">
    <div class="space-y-6 p-4 md:p-6">
        <div>
            <flux:heading>{{ __('Outfit calendar') }}</flux:heading>
            <flux:subheading>{{ __('Days with outfit logs') }}</flux:subheading>
        </div>
        <livewire:wardrobe-calendar lazy />
    </div>
</x-layouts::app>
