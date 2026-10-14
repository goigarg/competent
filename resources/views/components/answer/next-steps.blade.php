@props([
    'steps' => [],
])

{{--
    CI answer block D: at most four buttons. The first is the most likely action,
    the last is always "Mit einer Person sprechen".
--}}
<div class="mt-4 pt-3.5 border-t border-kp-bluegrey dark:border-zinc-800">
    <h4 class="sr-only">{{ __('answer.next_steps_heading') }}</h4>
    <div class="flex flex-wrap gap-3">
        @foreach($steps as $step)
            <button
                type="button"
                wire:click="selectNextStep(@js($step['label']))"
                wire:loading.attr="disabled"
                class="min-h-11 px-4 text-start text-sm sm:text-base font-semibold disabled:opacity-60 cursor-pointer {{ $step['primary'] ? 'bg-kp-navy text-white hover:bg-kp-darkblue dark:bg-white dark:text-kp-navy' : 'bg-white text-kp-navy border-2 border-kp-navy hover:bg-kp-bluegrey-60 dark:bg-zinc-900 dark:text-white dark:border-white' }}"
            >
                {{ $step['label'] }}
            </button>
        @endforeach

        <button
            type="button"
            wire:click="respondWithHumanSupport"
            wire:loading.attr="disabled"
            class="min-h-11 px-4 text-start text-sm sm:text-base font-semibold bg-white text-kp-navy border-2 border-kp-navy hover:bg-kp-bluegrey-60 disabled:opacity-60 cursor-pointer dark:bg-zinc-900 dark:text-white dark:border-white"
        >
            {{ __('answer.talk_to_person') }}
        </button>
    </div>
</div>
