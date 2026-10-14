@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.brand')" {{ $attributes }}>
        <x-slot name="logo" class="flex size-8 items-center justify-center">
            <x-kompetent-flag class="h-4 w-[1.54rem] fill-kp-flag" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.brand')" {{ $attributes }}>
        <x-slot name="logo" class="flex size-8 items-center justify-center">
            <x-kompetent-flag class="h-4 w-[1.54rem] fill-kp-flag" />
        </x-slot>
    </flux:brand>
@endif
