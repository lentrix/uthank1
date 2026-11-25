@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | Lending Demo</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-100 font-sans text-stone-900 antialiased dark:bg-stone-950 dark:text-stone-100">
        <header class="bg-teal-950 text-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-8">
                <span class="text-lg font-semibold tracking-tight">Lending Demo</span>

                <nav aria-label="Main navigation">
                    <a href="{{ route('debtors.index') }}"
                        @if (request()->routeIs('debtors.index')) aria-current="page" @endif
                        class="inline-flex rounded-lg px-4 py-2 text-sm font-semibold hover:bg-white/15 aria-[current=page]:bg-white/10">
                        Debtors
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-8 sm:py-12">
            {{ $slot }}
        </main>
    </body>
</html>
