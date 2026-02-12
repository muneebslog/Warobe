<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="h-16 w-48 animate-pulse rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
        <div class="flex gap-2">
            <div class="h-10 w-28 animate-pulse rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
            <div class="h-10 w-32 animate-pulse rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @for ($i = 0; $i < 4; $i++)
            <div class="h-24 animate-pulse rounded-xl bg-zinc-200 dark:bg-zinc-700"></div>
        @endfor
    </div>
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="h-24 animate-pulse rounded-xl bg-zinc-200 dark:bg-zinc-700"></div>
        <div class="h-24 animate-pulse rounded-xl bg-zinc-200 dark:bg-zinc-700"></div>
    </div>
    <div class="h-20 animate-pulse rounded-xl bg-zinc-200 dark:bg-zinc-700"></div>
</div>
