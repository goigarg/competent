@props([
    'block',
    'messageId',
    'sources' => [],
])

@php
    $labels = collect($sources)->mapWithKeys(fn ($s) => [$s['number'] => $s['label'] ?? $s['title']]);
@endphp

{{-- CI answer block A (steps, where and by when) with B (documents per step) and the step's source numbers. --}}
<div class="my-3 flex flex-col gap-2">
    @if(!empty($block['heading']))
        <h4 class="text-sm font-semibold text-kp-black dark:text-white">{{ $block['heading'] }}</h4>
    @endif

    <ol dir="auto" class="border border-kp-bluegrey bg-white dark:bg-zinc-900 dark:border-zinc-700">
        @foreach($block['items'] as $step)
            <li class="flex flex-col gap-2 px-5 py-4 border-b border-kp-bluegrey last:border-b-0 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <h5 class="text-base sm:text-lg font-bold text-kp-black dark:text-white">
                        {{ $step['number'] }} · {{ $step['title'] }}
                    </h5>

                    @if(!empty($step['sources']))
                        <span class="flex flex-none gap-1">
                            @foreach($step['sources'] as $n)
                                <a
                                    href="#src-{{ $messageId }}-{{ $n }}"
                                    class="inline-flex min-w-6 min-h-6 items-center justify-center bg-kp-navy px-1.5 text-xs font-bold text-white no-underline hover:bg-kp-darkblue dark:bg-white dark:text-kp-navy"
                                    aria-label="{{ __('answer.source_n', ['n' => $n]) }}: {{ $labels[$n] ?? '' }}"
                                >{{ $n }}</a>
                            @endforeach
                        </span>
                    @endif
                </div>

                @php
                    $meta = array_filter([
                        filled($step['where'] ?? null) ? __('answer.field_where').': '.$step['where'] : null,
                        filled($step['deadline'] ?? null) ? __('answer.field_deadline').': '.$step['deadline'] : null,
                    ]);
                @endphp

                @if($meta !== [])
                    <p class="text-sm sm:text-base text-kp-muted dark:text-zinc-300">{{ implode(' · ', $meta) }}</p>
                @endif

                @if(filled($step['detail'] ?? null))
                    <p class="text-sm sm:text-base text-kp-muted dark:text-zinc-300">{{ $step['detail'] }}</p>
                @endif

                @if(!empty($block['structured']))
                    <ul class="flex flex-wrap gap-2" aria-label="{{ __('answer.field_documents') }}">
                        @forelse($step['documents'] ?? [] as $document)
                            <li class="bg-kp-sand-30 px-3 py-1.5 text-sm text-kp-black dark:bg-zinc-800 dark:text-zinc-100">{{ $document }}</li>
                        @empty
                            <li class="bg-kp-sand-30 px-3 py-1.5 text-sm text-kp-black dark:bg-zinc-800 dark:text-zinc-100">{{ __('answer.documents_none') }}</li>
                        @endforelse
                    </ul>
                @endif
            </li>
        @endforeach
    </ol>
</div>
