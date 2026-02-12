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

            <div>
                <flux:label>{{ __('Image (optional)') }}</flux:label>
                <input type="file" wire:model="image" accept="image/*"
                    class="mt-1 block w-full text-sm text-zinc-500 file:me-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-zinc-700 dark:file:bg-zinc-700 dark:file:text-zinc-200" />
                <flux:error name="image" />
                <div wire:loading wire:target="image" class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Uploading & detecting color…') }}
                </div>
                @if ($image)
                    <div class="mt-3 flex flex-wrap items-start gap-4">
                        <img src="{{ $image->temporaryUrl() }}"
                            alt=""
                            class="w-40 h-40 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700" />
                        @if ($color_hex)
                            <div class="flex flex-col gap-2">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Detected color') }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-8 h-8 rounded-full border-2 border-zinc-300 dark:border-zinc-600 shrink-0"
                                        style="background-color: {{ $color_hex }};"></span>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ ucfirst($color_family) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <flux:label>{{ __('Color') }}</flux:label>
                @if ($image && $color_detection_error)
                    <p class="mt-1 mb-1 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2 py-1.5 rounded">
                        {{ __('Color detection failed: :message', ['message' => $color_detection_error]) }}
                    </p>
                @endif
                <flux:select wire:model="color_family" class="mt-1" required>
                    @foreach ($colorFamilyOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="color_family" />
            </div>

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

            <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('Add item') }}</span>
                <span wire:loading wire:target="save">{{ __('Adding…') }}</span>
            </flux:button>
        </form>
    </flux:card>
</div>