<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} — Smart Wardrobe Manager</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            {{-- Hero --}}
            <section class="text-center">
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl lg:text-6xl">
                    Smart Wardrobe Manager
                </h1>
                <p class="mt-4 text-lg text-zinc-400 sm:text-xl">
                    Track your clothes. Rotate intelligently. Never repeat unintentionally.
                </p>
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-medium text-zinc-900 shadow-sm transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-zinc-950"
                    >
                        Get Started
                    </a>
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-600 bg-zinc-900/50 px-6 py-3 text-base font-medium text-zinc-100 transition hover:border-zinc-500 hover:bg-zinc-800/50 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 focus:ring-offset-zinc-950"
                    >
                        Login
                    </a>
                </div>
            </section>

            {{-- Features --}}
            <section class="mt-24 sm:mt-32">
                <h2 class="sr-only">Features</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:gap-8">
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                        <h3 class="text-lg font-medium text-white">Calendar Tracking</h3>
                        <p class="mt-2 text-sm text-zinc-400">View what you wore each day.</p>
                    </div>
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                        <h3 class="text-lg font-medium text-white">Intelligent Suggestions</h3>
                        <p class="mt-2 text-sm text-zinc-400">Scoring-based outfit engine.</p>
                    </div>
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                        <h3 class="text-lg font-medium text-white">Rotation Control</h3>
                        <p class="mt-2 text-sm text-zinc-400">Avoid repeating recent outfits.</p>
                    </div>
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6">
                        <h3 class="text-lg font-medium text-white">Smart Reminders</h3>
                        <p class="mt-2 text-sm text-zinc-400">Dry clean & unused clothing alerts.</p>
                    </div>
                </div>
            </section>

            {{-- AI section --}}
            <section class="mt-24 rounded-xl border border-zinc-800 bg-zinc-900/30 px-6 py-8 text-center sm:mt-32 sm:px-10 sm:py-10">
                <h2 class="text-xl font-medium text-white sm:text-2xl">Hybrid AI Stylist</h2>
                <p class="mx-auto mt-3 max-w-xl text-sm text-zinc-400 sm:text-base">
                    Rule-based engine generates top options. AI selects the best one.
                </p>
                <span class="mt-4 inline-block rounded-full border border-zinc-600 bg-zinc-800/50 px-3 py-1 text-xs font-medium text-zinc-300">
                    AI optional & cost-controlled
                </span>
            </section>
        </div>
    </body>
</html>
