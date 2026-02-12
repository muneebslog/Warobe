<div>
    <div class="mb-4 flex items-center justify-between">
        <div class="h-8 w-40 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
        <div class="flex gap-2">
            <div class="h-9 w-24 animate-pulse rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
            <div class="h-9 w-20 animate-pulse rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
        </div>
    </div>
    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800/50">
            @for ($i = 0; $i < 7; $i++)
                <div class="h-10 animate-pulse bg-zinc-200 dark:bg-zinc-700"></div>
            @endfor
        </div>
        @for ($row = 0; $row < 5; $row++)
            <div class="grid grid-cols-7 border-b border-zinc-100 last:border-b-0 dark:border-zinc-700">
                @for ($col = 0; $col < 7; $col++)
                    <div class="min-h-[4rem] animate-pulse bg-zinc-100 dark:bg-zinc-800/50"></div>
                @endfor
            </div>
        @endfor
    </div>
</div>
