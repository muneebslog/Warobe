<x-layouts::app :title="__('Outfit suggestion')">
    <div class="p-4 md:p-6">
        <div class="mx-auto max-w-3xl">
            <div class="mb-6 text-center">
                <flux:heading>{{ __('Outfit suggestion') }}</flux:heading>
                <flux:subheading>{{ __('Pick an event type and get a suggested outfit') }}</flux:subheading>
            </div>

            <livewire:outfit-suggestion-panel lazy />
        </div>
    </div>
</x-layouts::app>
