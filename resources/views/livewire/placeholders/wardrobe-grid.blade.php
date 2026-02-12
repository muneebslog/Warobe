<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @for ($i = 0; $i < 8; $i++)
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="aspect-square w-full animate-pulse bg-zinc-200 dark:bg-zinc-700"></div>
            <div class="space-y-2 p-4">
                <div class="h-4 w-3/4 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                <div class="flex gap-1">
                    <div class="h-5 w-14 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                    <div class="h-5 w-14 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                    <div class="h-5 w-16 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                </div>
                <div class="mt-2 flex gap-1">
                    <div class="h-8 w-24 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                    <div class="h-8 w-20 animate-pulse rounded bg-zinc-200 dark:bg-zinc-700"></div>
                </div>
            </div>
        </div>
    @endfor
</div>
