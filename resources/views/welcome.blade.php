<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Offizielle KI-Bürgerassistenz der Republik Österreich für das Jahr 2030. Bürgernahe Verwaltung in einfacher Sprache, Reisepass-Erneuerung und Wohnsitz-Meldewesen.">
        <meta name="theme-color" content="#C8102E">

        <title>{{ config('app.name', 'Bundesservice 2030') }} - Republik Österreich</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-full font-sans antialiased bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 selection:bg-[#C8102E] selection:text-white">
        <livewire:austrian-gov-assistant />

        @fluxScripts
    </body>
</html>
