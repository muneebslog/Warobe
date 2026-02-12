<div>
    <div class="mb-8 flex flex-wrap items-end justify-center gap-4">
        <div class="min-w-[180px]">
            <flux:label>{{ __('Event type') }}</flux:label>
            <flux:select wire:model="eventType" class="mt-1 w-full">
                @foreach ($eventTypeOptions as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <flux:button variant="primary" wire:click="suggest" wire:loading.attr="disabled" icon="sparkles">
            <span wire:loading.remove wire:target="suggest">{{ __('Suggest outfit') }}</span>
            <span wire:loading wire:target="suggest">{{ __('Suggesting…') }}</span>
        </flux:button>
    </div>

    @if ($suggestion)
        @php
            $shirt = $suggestion['shirt'];
            $pant = $suggestion['pant'];
            $shalwarKameez = $suggestion['shalwar_kameez'];
            $hasSuggestion = $shirt || $pant || $shalwarKameez;
        @endphp

        @if ($hasSuggestion)
            <flux:card class="overflow-hidden p-0">
                <div class="p-6 text-center">
                    <flux:heading size="lg" class="mb-1">{{ __('Your suggested outfit') }}</flux:heading>
                    <flux:subheading>{{ __('Event: ') }}{{ \Illuminate\Support\Str::headline($eventType) }}</flux:subheading>
                    @if ($explanation)
                        <flux:text class="mt-3 block text-sm italic text-zinc-500">{{ $explanation }}</flux:text>
                    @endif
                </div>

                <div class="grid gap-0 border-t border-zinc-200 dark:border-zinc-700 sm:grid-cols-2 lg:grid-cols-3">
                    @if ($shalwarKameez)
                        <div class="flex flex-col border-zinc-200 dark:border-zinc-700 sm:border-s first:sm:border-s-0">
                            <div class="aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-700">
                                @if ($shalwarKameez->image_path)
                                    <img src="{{ asset('storage/' . $shalwarKameez->image_path) }}" alt="{{ $shalwarKameez->name }}" class="size-full object-cover" />
                                @else
                                    <div class="flex size-full items-center justify-center text-zinc-400">
                                        <flux:icon.photo class="size-20" />
                                    </div>
                                @endif
                            </div>
                            <div class="p-4 text-center">
                                <flux:badge color="zinc" size="sm">Shalwar Kameez</flux:badge>
                                <flux:heading size="md" class="mt-2">{{ $shalwarKameez->name }}</flux:heading>
                                <flux:text class="text-zinc-500">{{ $shalwarKameez->color }}</flux:text>
                            </div>
                        </div>
                    @else
                        @if ($shirt)
                            <div class="flex flex-col border-zinc-200 dark:border-zinc-700 sm:border-s first:sm:border-s-0">
                                <div class="aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-700">
                                    @if ($shirt->image_path)
                                        <img src="{{ asset('storage/' . $shirt->image_path) }}" alt="{{ $shirt->name }}" class="size-full object-cover" />
                                    @else
                                        <div class="flex size-full items-center justify-center text-zinc-400">
                                            <flux:icon.photo class="size-20" />
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4 text-center">
                                    <flux:badge color="zinc" size="sm">Shirt</flux:badge>
                                    <flux:heading size="md" class="mt-2">{{ $shirt->name }}</flux:heading>
                                    <flux:text class="text-zinc-500">{{ $shirt->color }}</flux:text>
                                </div>
                            </div>
                        @endif
                        @if ($pant)
                            <div class="flex flex-col border-zinc-200 dark:border-zinc-700 sm:border-s first:sm:border-s-0">
                                <div class="aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-700">
                                    @if ($pant->image_path)
                                        <img src="{{ asset('storage/' . $pant->image_path) }}" alt="{{ $pant->name }}" class="size-full object-cover" />
                                    @else
                                        <div class="flex size-full items-center justify-center text-zinc-400">
                                            <flux:icon.photo class="size-20" />
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4 text-center">
                                    <flux:badge color="zinc" size="sm">Pant</flux:badge>
                                    <flux:heading size="md" class="mt-2">{{ $pant->name }}</flux:heading>
                                    <flux:text class="text-zinc-500">{{ $pant->color }}</flux:text>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-center gap-3 border-t border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:button variant="outline" wire:click="suggest" wire:loading.attr="disabled" icon="arrow-path">
                        {{ __('Regenerate') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="wearThisOutfit" icon="check">
                        {{ __('Wear this outfit') }}
                    </flux:button>
                </div>
            </flux:card>
        @else
            <flux:callout variant="warning" icon="information-circle" class="text-center">
                {{ __('No matching outfit for this event type. Add more clothing items or try another type.') }}
            </flux:callout>
        @endif
    @else
        <flux:card class="py-12 text-center">
            <flux:icon.photo class="mx-auto size-12 text-zinc-400 dark:text-zinc-500" />
            <flux:heading size="md" class="mt-4">{{ __('Get started') }}</flux:heading>
            <flux:text class="mx-auto mt-2 max-w-sm text-zinc-500">
                {{ __('Select an event type above and click "Suggest outfit" to see a recommendation.') }}
            </flux:text>
        </flux:card>
    @endif
</div>
