@php($language = \App\Enums\Language::current())
<!DOCTYPE html>
<html lang="{{ $language->htmlLang() }}" dir="{{ $language->dir() }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ __('chat.meta_description', ['brand' => config('app.brand')]) }}">
        <meta name="theme-color" content="#ffffff">

        <title>{{ config('app.brand') }} · {{ __('chat.digital_administrative_assistant') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-full font-sans antialiased bg-white dark:bg-zinc-950 text-kp-black dark:text-zinc-100 selection:bg-kp-navy selection:text-white">
        <livewire:austrian-gov-assistant />

        @fluxScripts
    </body>
</html>
