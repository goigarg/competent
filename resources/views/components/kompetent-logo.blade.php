@props([
    'tagline' => false,
    'domain' => false,
    'inverted' => false,
])

{{--
    Kompetent logo (docs/ci): flag on the left, word mark in Inter Regular with tracking +60.
    Flag and text share one baseline; the flag height equals the cap height.
    On dark surfaces the text turns white and the flag stays red.
--}}
<span {{ $attributes->merge(['class' => 'inline-flex items-baseline gap-x-6 flex-wrap']) }}>
    {{-- Logo and flag are never mirrored, also in right-to-left languages (CI chapter 14). --}}
    <span dir="ltr" class="inline-flex items-baseline gap-[0.727em] tracking-[0.06em] font-normal {{ $inverted ? 'text-white' : 'text-kp-black dark:text-white' }}">
        <x-kompetent-flag class="h-[0.727em] w-[1.12em] flex-none fill-kp-flag" />
        <span>{{ config('app.brand') }}</span>
    </span>

    @if ($tagline)
        <span class="font-light leading-tight tracking-normal {{ $inverted ? 'text-white' : 'text-kp-black dark:text-white' }}">
            {{ is_string($tagline) ? $tagline : __('chat.digital_administrative_assistant') }}
        </span>
    @endif

    @if ($domain)
        <span class="text-kp-textred dark:text-kp-bluegrey">{{ config('app.brand_domain') }}</span>
    @endif
</span>
