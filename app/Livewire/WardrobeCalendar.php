<?php

namespace App\Livewire;

use App\Models\OutfitLog;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class WardrobeCalendar extends Component
{
    public int $currentYear;

    public int $currentMonth;

    public ?string $selectedDate = null;

    public bool $showModal = false;

    public function mount(): void
    {
        $this->currentYear = (int) Carbon::now()->format('Y');
        $this->currentMonth = (int) Carbon::now()->format('n');
    }

    public function getOutfitsForDate(string $date): Collection
    {
        return OutfitLog::query()
            ->where('user_id', auth()->id())
            ->whereDate('worn_at', $date)
            ->with(['shirt', 'pant', 'shalwarKameez'])
            ->orderBy('worn_at')
            ->get();
    }

    public function getOutfitsByDateForMonthProperty(): array
    {
        $start = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $logs = OutfitLog::query()
            ->where('user_id', auth()->id())
            ->whereBetween('worn_at', [$start, $end])
            ->with(['shirt', 'pant', 'shalwarKameez'])
            ->get();

        $byDate = [];
        foreach ($logs as $log) {
            $key = $log->worn_at->toDateString();
            if (! isset($byDate[$key])) {
                $byDate[$key] = [];
            }
            $byDate[$key][] = $log;
        }

        return $byDate;
    }

    public function getCalendarDaysProperty(): array
    {
        $start = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $end = $start->copy()->endOfMonth();
        $firstDayOfWeek = $start->dayOfWeek; // 0 = Sunday
        $daysInMonth = $end->day;
        $byDate = $this->outfitsByDateForMonth;

        $weeks = [];
        $week = array_fill(0, 7, null);
        $cellIndex = 0;

        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $week[$cellIndex++] = null;
        }

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($this->currentYear, $this->currentMonth, $d);
            $week[$cellIndex++] = [
                'day' => $d,
                'date' => $date->toDateString(),
                'count' => isset($byDate[$date->toDateString()]) ? count($byDate[$date->toDateString()]) : 0,
            ];
            if ($cellIndex === 7) {
                $weeks[] = $week;
                $week = array_fill(0, 7, null);
                $cellIndex = 0;
            }
        }
        if ($cellIndex > 0) {
            $weeks[] = $week;
        }

        return $weeks;
    }

    public function getMonthLabelProperty(): string
    {
        return Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }

    public function previousMonth(): void
    {
        $d = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentYear = (int) $d->format('Y');
        $this->currentMonth = (int) $d->format('n');
    }

    public function nextMonth(): void
    {
        $d = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear = (int) $d->format('Y');
        $this->currentMonth = (int) $d->format('n');
    }

    public function openDateModal(string $date): void
    {
        $this->selectedDate = $date;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedDate = null;
    }

    public function getSelectedDateOutfitsProperty(): Collection
    {
        if (! $this->selectedDate) {
            return collect();
        }
        return $this->getOutfitsForDate($this->selectedDate);
    }

    public function placeholder(): View
    {
        return view('livewire.placeholders.wardrobe-calendar');
    }

    public function render(): View
    {
        return view('livewire.wardrobe-calendar');
    }
}
