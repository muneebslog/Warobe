<div>
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">{{ $this->monthLabel }}</flux:heading>
        <div class="flex gap-2">
            <flux:button size="sm" variant="outline" wire:click="previousMonth" icon="chevron-left">
                {{ __('Previous') }}
            </flux:button>
            <flux:button size="sm" variant="outline" wire:click="nextMonth" icon="chevron-right">
                {{ __('Next') }}
            </flux:button>
        </div>
    </div>

    <flux:card class="overflow-hidden p-0">
        <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="p-2 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $day }}</div>
            @endforeach
        </div>
        @foreach ($this->calendarDays as $week)
            <div class="grid grid-cols-7 border-b border-zinc-100 last:border-b-0 dark:border-zinc-700">
                @foreach ($week as $cell)
                    @if ($cell)
                        <button
                            type="button"
                            wire:click="openDateModal('{{ $cell['date'] }}')"
                            class="min-h-[4rem] p-2 text-left transition hover:bg-zinc-100 dark:hover:bg-zinc-700/50 {{ $cell['count'] > 0 ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}"
                        >
                            <span class="text-sm">{{ $cell['day'] }}</span>
                            @if ($cell['count'] > 0)
                                <span class="ml-1 inline-flex size-5 items-center justify-center rounded-full bg-primary-500 text-xs font-medium text-white">
                                    {{ $cell['count'] }}
                                </span>
                            @endif
                        </button>
                    @else
                        <div class="min-h-[4rem] p-2 bg-zinc-50/50 dark:bg-zinc-800/30"></div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </flux:card>

    <flux:modal wire:model="showModal" name="calendar-date-modal" class="max-w-lg">
        @if ($selectedDate)
            <flux:heading size="lg">{{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}</flux:heading>
            <div class="mt-4 space-y-4">
                @forelse ($this->selectedDateOutfits as $log)
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:badge color="zinc" size="sm">{{ \Illuminate\Support\Str::headline($log->event_type) }}</flux:badge>
                        <flux:text class="mt-2 text-sm text-zinc-500">{{ $log->worn_at->format('g:i A') }}</flux:text>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @if ($log->shirt)
                                <div class="flex flex-col items-center gap-1">
                                    @if ($log->shirt->image_path)
                                        <img src="{{ asset('storage/' . $log->shirt->image_path) }}" alt="" class="size-16 rounded object-cover" />
                                    @else
                                        <div class="flex size-16 items-center justify-center rounded bg-zinc-200 dark:bg-zinc-700">
                                            <flux:icon.photo class="size-6 text-zinc-400" />
                                        </div>
                                    @endif
                                    <flux:text class="text-xs">Shirt: {{ $log->shirt->name }}</flux:text>
                                </div>
                            @endif
                            @if ($log->pant)
                                <div class="flex flex-col items-center gap-1">
                                    @if ($log->pant->image_path)
                                        <img src="{{ asset('storage/' . $log->pant->image_path) }}" alt="" class="size-16 rounded object-cover" />
                                    @else
                                        <div class="flex size-16 items-center justify-center rounded bg-zinc-200 dark:bg-zinc-700">
                                            <flux:icon.photo class="size-6 text-zinc-400" />
                                        </div>
                                    @endif
                                    <flux:text class="text-xs">Pant: {{ $log->pant->name }}</flux:text>
                                </div>
                            @endif
                            @if ($log->shalwarKameez)
                                <div class="flex flex-col items-center gap-1">
                                    @if ($log->shalwarKameez->image_path)
                                        <img src="{{ asset('storage/' . $log->shalwarKameez->image_path) }}" alt="" class="size-16 rounded object-cover" />
                                    @else
                                        <div class="flex size-16 items-center justify-center rounded bg-zinc-200 dark:bg-zinc-700">
                                            <flux:icon.photo class="size-6 text-zinc-400" />
                                        </div>
                                    @endif
                                    <flux:text class="text-xs">Shalwar Kameez: {{ $log->shalwarKameez->name }}</flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">{{ __('No outfits logged for this day.') }}</flux:text>
                @endforelse
            </div>
        @endif
        <div class="mt-4 flex justify-end">
            <flux:button variant="outline" wire:click="closeModal">{{ __('Close') }}</flux:button>
        </div>
    </flux:modal>
</div>
