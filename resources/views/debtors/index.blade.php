<x-layouts.app title="Debtors">
    <div class="flex flex-col gap-5">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Debtors</h1>
            <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">
                {{ number_format($debtors->total()) }} {{ Str::plural('debtor', $debtors->total()) }}
            </p>
        </div>

        <div class="overflow-x-auto rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
            <table class="w-full whitespace-nowrap text-left text-sm">
                <caption class="sr-only">Debtor directory</caption>
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-700 dark:border-stone-800 dark:bg-stone-900 dark:text-stone-300">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Name</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Location</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Occupation</th>
                        <th scope="col" class="px-5 py-3 text-right font-semibold">Credit limit</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                    @forelse ($debtors as $debtor)
                        <tr class="hover:bg-stone-50 dark:hover:bg-stone-800/50">
                            <th scope="row" class="px-5 py-4 font-semibold">{{ $debtor->lname }}, {{ $debtor->fname }}</th>
                            <td class="px-5 py-4">{{ $debtor->town }}, {{ $debtor->province }}</td>
                            <td class="px-5 py-4">{{ $debtor->occupation }}</td>
                            <td class="px-5 py-4 text-right tabular-nums">₱{{ number_format($debtor->credit_limit, 2) }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('debtors.show', $debtor) }}"
                                    aria-label="View {{ $debtor->fname }} {{ $debtor->lname }}"
                                    class="rounded-md bg-teal-50 px-3 py-2 font-semibold text-teal-800 hover:bg-teal-100 dark:bg-teal-950 dark:text-teal-200 dark:hover:bg-teal-900">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-stone-600 dark:text-stone-400">No debtors to display.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $debtors->links() }}
    </div>
</x-layouts.app>
