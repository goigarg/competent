<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Offizielles Beamtinnen- und Beamtenportal der Republik Österreich zur Bearbeitung eskalierter Bürger-Fälle.">
    <meta name="theme-color" content="#C8102E">

    <title>{{ $title ?? 'Beamtinnen- & Beamten-Portal - Bundesservice Österreich 2030' }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-full font-sans antialiased bg-slate-100 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 selection:bg-[#C8102E] selection:text-white">
    {{ $slot }}

    @fluxScripts
</body>
</html>
