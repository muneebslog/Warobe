<div>
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">{{ __('Add clothing item') }}</flux:heading>
        <form wire:submit="save" class="space-y-4">
            <flux:input wire:model="name" :label="__('Name')" type="text" required />
            <flux:error name="name" />

            <div>
                <flux:label>{{ __('Type') }}</flux:label>
                <flux:select wire:model="type" class="mt-1">
                    @foreach ($typeOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:input wire:model="color" :label="__('Color')" type="text" required />
            <flux:error name="color" />

            <div>
                <flux:label>{{ __('Formality') }}</flux:label>
                <flux:select wire:model="formality" class="mt-1">
                    @foreach ($formalityOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:label>{{ __('Season') }}</flux:label>
                <flux:select wire:model="season" class="mt-1">
                    @foreach ($seasonOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:label>{{ __('Image (optional)') }}</flux:label>
                <input type="file" wire:model="image" accept="image/*"
                    class="mt-1 block w-full text-sm text-zinc-500 file:me-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-zinc-700 dark:file:bg-zinc-700 dark:file:text-zinc-200" />
                <flux:error name="image" />
                @if ($image)
                    <div class="mt-3">
                        <flux:label>{{ __('Preview') }}</flux:label>

                        <img src="{{ $image->temporaryUrl() }}"
                            class="mt-2 w-40 h-40 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700">
                    </div>
                @endif

            </div>

            <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('Add item') }}</span>
                <span wire:loading wire:target="save">{{ __('Adding…') }}</span>
            </flux:button>
        </form>
    </flux:card>
</div>