@props([
    'cited' => [],
    'further' => [],
    'messageId',
])

{{--
    CI answer block C (chapter 16): every statement points to its source by number.
    Sources are matched by topic and not checked yet, so "Verifiziert" only appears once a backend sets `verified`.
--}}
@if(!empty($cited))
    <div class="mt-4 pt-3 border-t border-kp-bluegrey dark:border-zinc-800">
        <h4 class="sr-only">{{ __('answer.sources_heading') }}</h4>
        <p dir="auto" class="text-sm leading-relaxed">
            @foreach($cited as $src)
                <span id="src-{{ $messageId }}-{{ $src['number'] }}" class="scroll-mt-24 target:bg-kp-bluegrey-60">
                    <sup class="font-bold text-kp-black dark:text-white">{{ $src['number'] }}</sup>
                    <a
                        href="{{ $src['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-kp-textred underline hover:text-kp-flag dark:text-kp-bluegrey"
                        title="{{ $src['description'] ?? '' }}"
                    >{{ $src['label'] ?? $src['title'] }}</a>
                    @if(!empty($src['verified']))
                        <span class="ms-1 text-xs font-semibold text-kp-verified">{{ __('answer.verified') }}@if(!empty($src['checked_at'])) · {{ $src['checked_at'] }}@endif</span>
                    @endif
                </span>
                @if(!$loop->last)
                    <span class="text-kp-muted" aria-hidden="true"> · </span>
                @endif
            @endforeach
        </p>
    </div>
@elseif(!empty($further))
    <div class="mt-4 pt-3 border-t border-kp-bluegrey dark:border-zinc-800">
        <h4 class="text-xs font-semibold text-kp-muted dark:text-zinc-400 mb-1.5">
            {{ __('answer.further_sources') }}
        </h4>
        <ul dir="auto" class="flex flex-col gap-1 text-sm">
            @foreach($further as $src)
                <li class="flex flex-wrap items-baseline gap-x-2">
                    <a
                        href="{{ $src['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-kp-textred underline hover:text-kp-flag dark:text-kp-bluegrey"
                        title="{{ $src['description'] ?? '' }}"
                    >{{ $src['label'] ?? $src['title'] }}</a>
                    @if(!empty($src['tag']))
                        <span class="text-xs text-kp-muted dark:text-zinc-400">{{ $src['tag'] }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
