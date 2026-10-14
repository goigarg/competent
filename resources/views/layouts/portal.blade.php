<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('app.brand') }} Sachbearbeitung: übergebene Fälle bearbeiten.">
    <meta name="theme-color" content="#ffffff">

    <title>{{ $title ?? config('app.brand').' · Sachbearbeitung' }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-full font-sans antialiased bg-slate-100 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 selection:bg-kp-navy selection:text-white">
    {{ $slot }}

    @fluxScripts
</body>
</html>
