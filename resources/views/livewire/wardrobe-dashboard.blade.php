<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading>{{ __('Wardrobe overview') }}</flux:heading>
            <flux:subheading>{{ __('Your clothing and outfit stats') }}</flux:subheading>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button variant="primary" :href="route('wardrobe.create')" wire:navigate icon="plus">
                {{ __('Add clothing') }}
            </flux:button>
            <flux:button variant="outline" :href="route('outfits.suggest')" wire:navigate icon="sparkles">
                {{ __('Get outfit suggestion') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <flux:card class="p-4">
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Total items') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $totalItems }}</flux:heading>
        </flux:card>
        <flux:card class="p-4">
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Clean') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $cleanCount }}</flux:heading>
        </flux:card>
        <flux:card class="p-4">
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Dry clean') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $dryCleanCount }}</flux:heading>
        </flux:card>
        <flux:card class="p-4">
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Laundry') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $laundryCount }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <flux:card class="p-4">
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Total dry clean cost') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $totalDryCleanCost }}</flux:heading>
        </flux:card>

        <flux:card class="p-4">
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Most worn item') }}</flux:text>
            @if ($mostWornItem)
                <flux:heading size="lg" class="mt-1">{{ $mostWornItem->name }}</flux:heading>
                <flux:text class="mt-0.5 text-sm text-zinc-500">{{ \Illuminate\Support\Str::headline($mostWornItem->type) }} · {{ $mostWornItem->color }}</flux:text>
            @else
                <flux:text class="mt-1">{{ __('None yet') }}</flux:text>
            @endif
        </flux:card>
    </div>

    <flux:card class="p-4">
        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Last worn outfit') }}</flux:text>
        @if ($lastWornOutfit)
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if ($lastWornOutfit->shirt)
                    <flux:badge color="zinc">{{ $lastWornOutfit->shirt->name }}</flux:badge>
                @endif
                @if ($lastWornOutfit->pant)
                    <flux:badge color="zinc">{{ $lastWornOutfit->pant->name }}</flux:badge>
                @endif
                @if ($lastWornOutfit->shalwarKameez)
                    <flux:badge color="zinc">{{ $lastWornOutfit->shalwarKameez->name }}</flux:badge>
                @endif
                <flux:text class="text-sm text-zinc-500">{{ $lastWornOutfit->worn_at->diffForHumans() }}</flux:text>
            </div>
        @else
            <flux:text class="mt-1">{{ __('None yet') }}</flux:text>
        @endif
    </flux:card>
</div>
