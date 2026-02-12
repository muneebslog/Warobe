<div>
    {{-- Filters --}}
    <div class="mb-6 flex flex-wrap items-end gap-4">
        <div class="min-w-[140px]">
            <flux:select wire:model.live="filterType" placeholder="Type">
                <flux:select.option value="">All types</flux:select.option>
                @foreach ($typeOptions as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div class="min-w-[140px]">
            <flux:select wire:model.live="filterStatus" placeholder="Status">
                <flux:select.option value="">All statuses</flux:select.option>
                @foreach ($statusOptions as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div class="min-w-[160px]">
            <flux:select wire:model.live="filterFormality" placeholder="Formality">
                <flux:select.option value="">All formality</flux:select.option>
                @foreach ($formalityOptions as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- Grid --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($clothingItems as $item)
            <flux:card wire:key="item-{{ $item->id }}" class="flex flex-col overflow-hidden p-0">
                <div class="aspect-square w-full overflow-hidden bg-zinc-100 dark:bg-zinc-700">
                    @if ($item->image_path)
                        <img
                            src="{{ asset('storage/' . $item->image_path) }}"
                            alt="{{ $item->name }}"
                            class="size-full object-cover"
                        />
                    @else
                        <div class="flex size-full items-center justify-center text-zinc-400 dark:text-zinc-500">
                            <flux:icon.photo class="size-16" />
                        </div>
                    @endif
                </div>
                <div class="flex flex-1 flex-col gap-2 p-4">
                    <flux:heading size="sm" class="line-clamp-1">{{ $item->name }}</flux:heading>
                    <div class="flex flex-wrap gap-1">
                        <flux:badge color="zinc" size="sm">{{ \Illuminate\Support\Str::headline($item->type) }}</flux:badge>
                        <flux:badge color="zinc" size="sm">{{ $item->color }}</flux:badge>
                        <flux:badge :color="\App\Livewire\WardrobeGrid::statusBadgeColor($item->status)" size="sm">
                            {{ \Illuminate\Support\Str::headline($item->status) }}
                        </flux:badge>
                        @if ($item->isOverdue())
                            <flux:badge color="red" size="sm">Overdue</flux:badge>
                        @endif
                    </div>
                    <div class="mt-auto flex flex-wrap gap-1 pt-2">
                        <flux:button size="sm" variant="outline" wire:click="markAsWorn({{ $item->id }})">
                            {{ __('Mark as Worn') }}
                        </flux:button>
                        <flux:button size="sm" variant="outline" wire:click="openDryCleanModal({{ $item->id }})">
                            {{ __('Dry Clean') }}
                        </flux:button>
                        @if ($item->status === 'dry_clean')
                            <flux:button size="sm" variant="primary" wire:click="markAsReceived({{ $item->id }})">
                                {{ __('Mark Received') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            </flux:card>
        @empty
            <div class="col-span-full rounded-xl border border-zinc-200 bg-zinc-50/50 py-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                <flux:text class="text-zinc-500">{{ __('No clothing items yet. Add one above.') }}</flux:text>
            </div>
        @endforelse
    </div>

    @if ($clothingItems->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$clothingItems" />
        </div>
    @endif

    {{-- Send to Dry Clean modal --}}
    <flux:modal wire:model="showDryCleanModal" name="dry-clean-modal" class="max-w-md">
        <form wire:submit="sendToDryClean" class="space-y-4">
            <flux:heading size="lg">{{ __('Send to Dry Clean') }}</flux:heading>
            <flux:input
                wire:model="dryCleanExpectedReturnDate"
                type="date"
                :label="__('Expected return date')"
            />
            <flux:input
                wire:model="dryCleanCost"
                type="number"
                step="0.01"
                min="0"
                :label="__('Cost (optional)')"
            />
            <flux:textarea wire:model="dryCleanNotes" :label="__('Notes (optional)')" rows="3" />
            <div class="flex justify-end gap-2">
                <flux:button variant="outline" type="button" wire:click="closeDryCleanModal">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">{{ __('Send to Dry Clean') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
