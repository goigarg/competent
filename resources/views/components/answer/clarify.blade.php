@props([
    'clarify',
    'messageId',
    'awaiting' => false,
])

{{-- CI phase 1 "Verstehen": confirm the recognised concerns before answering. --}}
<div class="flex flex-col gap-4">
    <div dir="auto" class="bg-kp-bluegrey-30 dark:bg-zinc-800 px-5 py-4 text-base leading-relaxed text-kp-black dark:text-zinc-100">
        @if(trim($clarify['intro']) !== '')
            <div class="[&_p]:m-0">{!! $clarify['intro_html'] !!}</div>
        @endif

        @if(!empty($clarify['items']))
            <ol class="mt-1 list-decimal ps-5">
                @foreach($clarify['items'] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ol>
        @endif
    </div>

    @if($awaiting && !empty($clarify['items']))
        <div class="flex flex-wrap gap-3" role="group" aria-label="{{ __('answer.confirm_summary') }}">
            <button
                type="button"
                wire:click="confirmUnderstanding(@js($messageId))"
                wire:loading.attr="disabled"
                class="min-h-11 px-5 bg-kp-navy text-white font-semibold hover:bg-kp-darkblue disabled:opacity-60 cursor-pointer"
            >
                {{ __('answer.confirm_yes') }}
            </button>
            <button
                type="button"
                wire:click="rejectUnderstanding(@js($messageId))"
                wire:loading.attr="disabled"
                class="min-h-11 px-5 bg-white text-kp-navy font-semibold border-2 border-kp-navy hover:bg-kp-bluegrey-60 disabled:opacity-60 cursor-pointer dark:bg-zinc-900 dark:text-white dark:border-white"
            >
                {{ __('answer.confirm_no') }}
            </button>
        </div>
    @endif
</div>
