@php
    $isGerman = $locale === 'de';
@endphp

<div 
    wire:poll.2s="checkForUpdates"
    class="min-h-screen flex flex-col {{ $highContrast ? 'bg-black text-white' : 'bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100' }} {{ $textSize === 'large' ? 'text-lg' : ($textSize === 'xlarge' ? 'text-xl' : 'text-base') }}"
    x-data="{
        copiedDossier: false,
        speak(text) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const cleanText = text.replace(/[#*`_\[\]]/g, '');
                const utterance = new SpeechSynthesisUtterance(cleanText);
                utterance.lang = '{{ $isGerman ? 'de-AT' : 'en-GB' }}';
                window.speechSynthesis.speak(utterance);
            }
        },
        copyText(text) {
            navigator.clipboard.writeText(text);
        }
    }"
>
    {{-- Skip link for keyboard accessibility --}}
    <a href="#chat-input-area" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:p-3 focus:bg-[#C8102E] focus:text-white focus:rounded-md focus:shadow-lg">
        {{ $isGerman ? 'Direkt zum Eingabefeld springen' : 'Skip directly to input field' }}
    </a>

    {{-- Official Republic of Austria Top Banner --}}
    <div class="bg-[#C8102E] text-white py-2 px-4 shadow-sm border-b border-red-800">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm font-medium">
            <div class="flex items-center gap-2.5">
                {{-- Austrian Flag Icon --}}
                <span class="inline-flex flex-col w-5 h-3.5 rounded-[2px] overflow-hidden shadow-xs border border-white/20 shrink-0" aria-hidden="true">
                    <span class="h-1 bg-[#C8102E] w-full"></span>
                    <span class="h-1.5 bg-white w-full"></span>
                    <span class="h-1 bg-[#C8102E] w-full"></span>
                </span>
                <span>
                    <strong>REPUBLIK ÖSTERREICH</strong> &bull; 
                    <span class="opacity-95">{{ $isGerman ? 'Offizielles Bürgerservice-Portal • Vision 2030' : 'Official Citizen Service Portal • Vision 2030' }}</span>
                </span>
            </div>

            <div class="flex items-center gap-4 text-xs">
                <span class="hidden sm:inline-flex items-center gap-1.5 opacity-90">
                    <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $isGerman ? 'Gesicherter Behördenkanal (eIDAS)' : 'Secured Public Channel (eIDAS)' }}
                </span>
                <span class="opacity-80">|</span>
                <a href="https://www.oesterreich.gv.at" target="_blank" rel="noopener noreferrer" class="hover:underline opacity-95">
                    oesterreich.gv.at &nearr;
                </a>
            </div>
        </div>
    </div>

    {{-- Fixed Top Loading Progress Bar when querying DeepSeek --}}
    <div wire:loading.flex wire:target="sendMessage, selectSuggestedQuestion, selectNextStep" class="fixed top-0 left-0 right-0 z-50 h-1.5 bg-red-100/80 dark:bg-zinc-800 overflow-hidden">
        <div class="h-full bg-gradient-to-r from-red-600 via-[#C8102E] to-amber-500 rounded-full animate-indeterminate"></div>
    </div>

    {{-- Main Navigation & Accessibility Toolbar --}}
    <header class="sticky top-0 z-30 {{ $highContrast ? 'bg-zinc-900 border-zinc-700' : 'bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-slate-200 dark:border-zinc-800' }} border-b shadow-xs transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
            {{-- Brand / Title --}}
            <div class="flex items-center gap-3">
                <div class="size-10 sm:size-11 rounded-lg bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 flex items-center justify-center shrink-0 shadow-inner">
                    <svg class="size-6 text-[#C8102E]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2L2 7v7c0 5.25 3.75 10.15 10 11.5 6.25-1.35 10-6.25 10-11.5V7L12 2zm0 3.3L19 9v5c0 4.1-2.9 7.9-7 9.1-4.1-1.2-7-5-7-9.1V9l7-3.7zM7 11h10v2H7v-2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-white">
                            Bundesservice AI 2030
                        </h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-[#C8102E] dark:bg-red-950 dark:text-red-300 border border-red-200 dark:border-red-800">
                            {{ $isGerman ? 'KI-Pilot 2030' : 'AI Pilot 2030' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 hidden sm:block">
                        {{ $isGerman ? 'Verständliche Verwaltung in Alltagssprache • '.$this->getModelDisplayName() : 'Plain language Austrian government services • '.$this->getModelDisplayName() }}
                    </p>
                </div>
            </div>

            {{-- Controls: Language Switcher, Accessibility & Human Support --}}
            <div class="flex items-center flex-wrap gap-2 sm:gap-3">
                {{-- Language Switcher (DE / EN) --}}
                <div class="inline-flex p-0.5 rounded-lg border border-slate-300 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800 text-xs font-semibold" role="group" aria-label="{{ $isGerman ? 'Sprachauswahl' : 'Language Selection' }}">
                    <button 
                        type="button" 
                        wire:click="setLocale('de')"
                        class="px-2.5 py-1 rounded-md transition-all flex items-center gap-1.5 {{ $isGerman ? 'bg-white dark:bg-zinc-700 text-[#C8102E] dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900' }}"
                        aria-pressed="{{ $isGerman ? 'true' : 'false' }}"
                    >
                        <span>🇦🇹</span>
                        <span>DE</span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="setLocale('en')"
                        class="px-2.5 py-1 rounded-md transition-all flex items-center gap-1.5 {{ !$isGerman ? 'bg-white dark:bg-zinc-700 text-[#C8102E] dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900' }}"
                        aria-pressed="{{ !$isGerman ? 'true' : 'false' }}"
                    >
                        <span>🇬🇧</span>
                        <span>EN</span>
                    </button>
                </div>

                {{-- Accessibility Controls Dropdown / Buttons --}}
                <div class="flex items-center gap-1 border border-slate-300 dark:border-zinc-700 rounded-lg p-1 bg-white dark:bg-zinc-800">
                    {{-- Text Size Cycle --}}
                    <button 
                        type="button"
                        wire:click="setTextSize('{{ $textSize === 'normal' ? 'large' : ($textSize === 'large' ? 'xlarge' : 'normal') }}')"
                        class="p-1.5 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-700 text-xs font-bold text-slate-700 dark:text-zinc-300"
                        title="{{ $isGerman ? 'Schriftgröße anpassen' : 'Adjust Text Size' }}"
                        aria-label="{{ $isGerman ? 'Schriftgröße anpassen' : 'Adjust Text Size' }}"
                    >
                        <span class="sr-only">{{ $isGerman ? 'Schriftgröße' : 'Text size' }}</span>
                        @if($textSize === 'normal')
                            <span>A</span>
                        @elseif($textSize === 'large')
                            <span class="text-[#C8102E] font-extrabold">A+</span>
                        @else
                            <span class="text-[#C8102E] font-black">A++</span>
                        @endif
                    </button>

                    {{-- High Contrast Toggle --}}
                    <button 
                        type="button"
                        wire:click="toggleHighContrast"
                        class="p-1.5 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 {{ $highContrast ? 'bg-yellow-400 text-black' : '' }}"
                        title="{{ $isGerman ? 'Hoher Kontrast umschalten' : 'Toggle High Contrast' }}"
                        aria-label="{{ $isGerman ? 'Hoher Kontrast' : 'High Contrast' }}"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    {{-- Leichte Sprache Toggle --}}
                    <button 
                        type="button"
                        wire:click="toggleSimpleLanguage"
                        class="px-2 py-1 rounded-md text-xs font-medium transition-all {{ $simpleLanguage ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-100 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300' }}"
                        title="{{ $isGerman ? 'Leichte Sprache aktivieren' : 'Simple Language Mode' }}"
                    >
                        {{ $isGerman ? 'Einfach' : 'Plain' }}
                    </button>
                </div>

                {{-- Human Assistance Button --}}
                <button 
                    type="button" 
                    wire:click="respondWithHumanSupport"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-300 dark:border-red-800 text-[#C8102E] dark:text-red-400 bg-red-50/60 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-xs font-semibold transition-colors"
                >
                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>{{ $isGerman ? 'Menschliche Hilfe' : 'Human Support' }}</span>
                </button>

                {{-- Civil Servant Employee Portal Link --}}
                <a 
                    href="{{ route('portal.cases') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 dark:border-zinc-700 text-slate-700 dark:text-zinc-200 bg-slate-50 dark:bg-zinc-800 hover:bg-slate-100 dark:hover:bg-zinc-700 text-xs font-semibold transition-colors shadow-2xs"
                    title="{{ $isGerman ? 'Zum Beamtinnen- & Beamten-Portal für eskalierte Fälle' : 'To Civil Servant Portal for escalated cases' }}"
                >
                    <span>🏛️</span>
                    <span class="hidden md:inline">{{ $isGerman ? 'Beamten-Portal' : 'Officer Portal' }}</span>
                    @if($escalatedCasesCount > 0)
                        <span class="size-2 rounded-full bg-red-600 animate-pulse ml-0.5" title="{{ $escalatedCasesCount }} {{ $isGerman ? 'offene Fälle' : 'pending cases' }}"></span>
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-red-100 dark:bg-red-950 text-[#C8102E] dark:text-red-300 border border-red-200 dark:border-red-900">{{ $escalatedCasesCount }}</span>
                    @endif
                </a>

                {{-- New Conversation Button --}}
                <button 
                    type="button" 
                    wire:click="startNewConversation"
                    wire:loading.attr="disabled"
                    wire:target="startNewConversation"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:text-[#C8102E] dark:hover:text-red-400 border border-slate-300 dark:border-zinc-700 hover:border-red-300 dark:hover:border-red-800 rounded-lg bg-white dark:bg-zinc-800 hover:bg-red-50/50 dark:hover:bg-red-950/30 transition-all cursor-pointer shadow-2xs disabled:opacity-50"
                    title="{{ $isGerman ? 'Neues Anliegen starten (bisherige Chats bleiben in der Datenbank gespeichert)' : 'Start new inquiry (existing chats remain saved in database)' }}"
                >
                    <span wire:loading.remove wire:target="startNewConversation" class="flex items-center gap-1.5">
                        <svg class="size-3.5 text-[#C8102E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ $isGerman ? 'Neues Anliegen' : 'New Chat' }}</span>
                    </span>
                    <span wire:loading.flex wire:target="startNewConversation" class="items-center gap-1.5">
                        <svg class="animate-spin size-3.5 text-[#C8102E]" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ $isGerman ? 'Wird neu gestartet...' : 'Starting...' }}</span>
                    </span>
                </button>
            </div>
        </div>
    </header>

    {{-- Main Content Layout --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col gap-6">

        {{-- Quick Service Shortcuts (Workflows & Key Services) --}}
        <section aria-label="{{ $isGerman ? 'Beliebte Behördendienste' : 'Popular Public Services' }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                {{-- Shortcut 1: Passport Renewal --}}
                <button 
                    type="button"
                    wire:click="startWorkflow('passport')"
                    class="text-left p-4 rounded-xl border {{ $activeWorkflow === 'passport' ? 'border-[#C8102E] ring-2 ring-[#C8102E]/20 bg-red-50/40 dark:bg-red-950/20' : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-red-300 dark:hover:border-red-800' }} shadow-xs transition-all group flex flex-col justify-between"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="size-9 rounded-lg bg-red-100 dark:bg-red-950/60 text-[#C8102E] flex items-center justify-center shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-semibold text-[#C8102E] uppercase tracking-wider bg-red-50 dark:bg-red-950 px-2 py-0.5 rounded">
                            {{ $isGerman ? 'Geführt' : 'Guided' }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#C8102E] transition-colors">
                            {{ $isGerman ? 'Reisepass erneuern' : 'Passport Renewal' }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            {{ $isGerman ? 'Gebühren (€75,90), Express-Optionen & Checkliste' : 'Fees (€75.90), express options & checklist' }}
                        </p>
                    </div>
                </button>

                {{-- Shortcut 2: Change of Residence --}}
                <button 
                    type="button"
                    wire:click="startWorkflow('residence')"
                    class="text-left p-4 rounded-xl border {{ $activeWorkflow === 'residence' ? 'border-[#C8102E] ring-2 ring-[#C8102E]/20 bg-red-50/40 dark:bg-red-950/20' : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-red-300 dark:hover:border-red-800' }} shadow-xs transition-all group flex flex-col justify-between"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="size-9 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider bg-amber-50 dark:bg-amber-950 px-2 py-0.5 rounded">
                            {{ $isGerman ? '3-Tage-Frist' : '3-Day Limit' }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#C8102E] transition-colors">
                            {{ $isGerman ? 'Wohnsitz ummelden' : 'Change of Residence' }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            {{ $isGerman ? 'Meldezettel, Vermieter-Unterschrift & ID Austria' : 'Meldezettel, landlord signature & online' }}
                        </p>
                    </div>
                </button>

                {{-- Shortcut 3: ID Austria Info --}}
                <button 
                    type="button"
                    wire:click="selectSuggestedQuestion('{{ $isGerman ? 'Wie richte ich die ID Austria ein und was brauche ich dafür?' : 'How do I set up ID Austria and what are the requirements?' }}')"
                    class="text-left p-4 rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-red-300 dark:hover:border-red-800 shadow-xs transition-all group flex flex-col justify-between"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="size-9 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider bg-blue-50 dark:bg-blue-950 px-2 py-0.5 rounded">
                            {{ $isGerman ? 'Digital' : 'Digital' }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#C8102E] transition-colors">
                            {{ $isGerman ? 'ID Austria einrichten' : 'Set Up ID Austria' }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            {{ $isGerman ? 'Digitaler Ausweis & Vollfunktion aktivieren' : 'Digital ID & activation prerequisites' }}
                        </p>
                    </div>
                </button>

                {{-- Shortcut 4: Generate Civil Servant Dossier --}}
                <button 
                    type="button"
                    wire:click="generateDossier"
                    class="text-left p-4 rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-red-300 dark:hover:border-red-800 shadow-xs transition-all group flex flex-col justify-between"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="size-9 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded">
                            {{ $isGerman ? 'Export' : 'Export' }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#C8102E] transition-colors">
                            {{ $isGerman ? 'Behörden-Dossier' : 'Case Dossier' }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            {{ $isGerman ? 'Zusammenfassung für den Termin generieren' : 'Generate briefing for civil servant appointment' }}
                        </p>
                    </div>
                </button>
            </div>
        </section>

        {{-- Interactive Active Workflow Banner --}}
        @if($activeWorkflow)
            <div class="p-4 rounded-xl border border-red-200 dark:border-red-900/60 bg-red-50/80 dark:bg-red-950/30 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="size-3 rounded-full bg-[#C8102E] animate-ping"></span>
                    <div>
                        <span class="text-xs font-semibold text-[#C8102E] uppercase tracking-wider">
                            {{ $isGerman ? 'Aktiver geführter Prozess' : 'Active Guided Workflow' }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ $activeWorkflow === 'passport' ? ($isGerman ? 'Reisepass-Verfahren' : 'Passport Issuance Workflow') : ($isGerman ? 'Wohnsitz-Meldewesen' : 'Residence Registration Workflow') }}
                        </h3>
                    </div>
                </div>
                <button 
                    type="button" 
                    wire:click="resetWorkflow"
                    class="text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white underline underline-offset-2"
                >
                    {{ $isGerman ? 'Workflow beenden' : 'Exit Workflow' }}
                </button>
            </div>
        @endif

        {{-- Live Officer Joined Notice Banner --}}
        @if($isOfficerJoined)
            <div class="p-3 sm:p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-100 flex items-center justify-between gap-3 shadow-xs animate-in fade-in duration-300">
                <div class="flex items-center gap-3">
                    <span class="size-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shrink-0 shadow-2xs">
                        🏛️
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
                                {{ $isGerman ? 'Amtliche Sachbearbeitung beigetreten' : 'Civil Servant Connected' }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm font-medium mt-0.5">
                            {{ $isGerman ? 'Beamtin/Beamter ' . ($officerJoinedName ?: 'Amtliche Sachbearbeitung') . ' betreut Ihr Anliegen nun persönlich.' : 'Civil servant ' . ($officerJoinedName ?: 'Official Officer') . ' has joined and is assisting you personally.' }}
                        </p>
                    </div>
                </div>
                <span class="hidden md:inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                    {{ $isGerman ? 'Live-Kanal aktiv' : 'Live Channel Active' }}
                </span>
            </div>
        @endif

        {{-- Conversational Stream Area --}}
        <div 
            class="flex-1 flex flex-col gap-4 overflow-y-auto"
            aria-live="polite"
            role="region"
            aria-label="{{ $isGerman ? 'Gesprächsverlauf' : 'Conversation Stream' }}"
        >
            @foreach($messages as $msg)
                <div class="flex flex-col gap-2 {{ $msg['role'] === 'user' ? 'items-end' : 'items-start' }}">
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-400 px-1">
                        @if($msg['role'] === 'user')
                            <span>{{ $isGerman ? 'Bürgerin / Bürger' : 'Citizen' }}</span>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @elseif($msg['role'] === 'system')
                            <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ $isGerman ? 'System-Hinweis' : 'System Notice' }}</span>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @elseif(($msg['workflow'] ?? null) === 'officer_joined' || str_contains($msg['content'], 'ist dem Gespräch beigetreten') || str_contains($msg['content'], 'has joined the conversation'))
                            <div class="flex items-center gap-1.5 font-bold text-emerald-700 dark:text-emerald-400">
                                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ $isGerman ? 'Menschliche Sachbearbeitung beigetreten' : 'Civil Servant Joined' }}</span>
                            </div>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @elseif(str_contains($msg['content'], 'Offizielle Mitteilung der Sachbearbeitung'))
                            <div class="flex items-center gap-1.5 font-bold text-emerald-700 dark:text-emerald-400">
                                <span class="size-2 rounded-full bg-emerald-600"></span>
                                <span>{{ $isGerman ? 'Offizielle Sachbearbeiter-Antwort' : 'Civil Servant Message' }}</span>
                            </div>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @else
                            <div class="flex items-center gap-1.5 font-semibold text-[#C8102E] dark:text-red-400">
                                <span class="size-2 rounded-full bg-[#C8102E]"></span>
                                <span>{{ $isGerman ? 'Offizielle Bürgerassistenz (Österreich 2030)' : 'Official Citizen Assistant (Austria 2030)' }}</span>
                            </div>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @endif
                    </div>

                    {{-- Message Bubble --}}
                    <div class="max-w-3xl rounded-2xl p-4 sm:p-5 shadow-xs transition-all relative group {{ $msg['role'] === 'user' ? 'bg-[#C8102E] text-white rounded-tr-none' : ($msg['role'] === 'system' ? 'bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 text-blue-900 dark:text-blue-200' : ((($msg['workflow'] ?? null) === 'officer_joined' || str_contains($msg['content'], 'ist dem Gespräch beigetreten')) ? 'bg-emerald-50/90 dark:bg-emerald-950/50 border-2 border-emerald-400 dark:border-emerald-700 text-emerald-950 dark:text-emerald-100 rounded-tl-none' : (str_contains($msg['content'], 'Offizielle Mitteilung der Sachbearbeitung') ? 'bg-amber-50/80 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-800 text-slate-800 dark:text-zinc-200 rounded-tl-none' : 'bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-slate-800 dark:text-zinc-200 rounded-tl-none'))) }}">
                        @php
                            $nextSteps = [];
                        @endphp

                        {{-- Message Content with Card-based Steps & Checklists --}}
                        @if($msg['role'] === 'user')
                            <div class="prose prose-sm text-white max-w-none leading-relaxed prose-p:my-1 prose-headings:text-white prose-strong:text-white prose-a:text-white underline">
                                {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                            </div>
                        @elseif($msg['role'] === 'system')
                            <div class="prose prose-sm text-blue-900 dark:text-blue-200 max-w-none leading-relaxed">
                                {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                            </div>
                        @elseif(($msg['workflow'] ?? null) === 'officer_joined' || str_contains($msg['content'], 'ist dem Gespräch beigetreten') || str_contains($msg['content'], 'has joined the conversation'))
                            <div class="flex items-start gap-3.5">
                                <div class="size-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg font-bold shrink-0 shadow-2xs">
                                    🏛️
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>{{ $isGerman ? 'Amtlicher Beitritt vermerkt' : 'Official Join Recorded' }}</span>
                                    </div>
                                    <div class="text-xs sm:text-sm font-semibold leading-relaxed">
                                        {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                                    </div>
                                    <p class="text-[11px] text-emerald-700 dark:text-emerald-400">
                                        {{ $isGerman ? 'Die Beamtin / der Beamte liest Ihre Nachrichten direkt mit und kann Ihnen antworten.' : 'The officer is actively viewing this case and can reply to your messages.' }}
                                    </p>
                                </div>
                            </div>
                        @else
                            @php
                                $parsedData = \App\Services\AustrianGov\MessageParser::parseWithNextSteps($msg['content'], $locale);
                                $parsedBlocks = $parsedData['blocks'];
                                $nextSteps = $parsedData['next_steps'] ?? [];
                            @endphp

                            <div class="space-y-3">
                                @forelse($parsedBlocks as $block)
                                    @if($block['type'] === 'prose')
                                        <div class="prose prose-sm dark:prose-invert max-w-none leading-relaxed prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:font-bold prose-headings:mt-3 prose-headings:mb-1.5 prose-p:my-1.5 prose-ul:my-2 prose-li:my-0.5">
                                            {!! $block['html'] !!}
                                        </div>

                                    @elseif($block['type'] === 'steps')
                                        <div class="my-3 space-y-2.5">
                                            @if(!empty($block['heading']))
                                                <div class="flex items-center gap-2 pt-1 pb-0.5">
                                                    <span class="size-6 rounded-md bg-[#C8102E] text-white flex items-center justify-center text-xs font-bold shrink-0">
                                                        &rarr;
                                                    </span>
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                                        {{ $block['heading'] }}
                                                    </h4>
                                                    <span class="text-[11px] font-semibold text-[#C8102E] bg-red-50 dark:bg-red-950 px-2 py-0.5 rounded-full border border-red-200 dark:border-red-900/60 ml-auto">
                                                        {{ count($block['items']) }} {{ $isGerman ? 'Schritte' : 'Steps' }}
                                                    </span>
                                                </div>
                                            @endif

                                            <div class="grid grid-cols-1 gap-2.5">
                                                @foreach($block['items'] as $step)
                                                    <div 
                                                        x-data="{ done: false }" 
                                                        class="p-3.5 rounded-xl border transition-all flex items-start gap-3.5 shadow-2xs"
                                                        :class="done ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800' : 'bg-slate-50 dark:bg-zinc-800/70 border-slate-200 dark:border-zinc-700 hover:border-red-300 dark:hover:border-red-800 hover:shadow-xs'"
                                                    >
                                                        <div 
                                                            @click="done = !done"
                                                            class="size-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 cursor-pointer transition-colors shadow-2xs"
                                                            :class="done ? 'bg-emerald-600 text-white' : 'bg-[#C8102E] text-white'"
                                                            title="{{ $isGerman ? 'Klicken zum Abhaken' : 'Click to complete' }}"
                                                        >
                                                            <span x-show="!done">{{ $step['number'] }}</span>
                                                            <span x-show="done">&check;</span>
                                                        </div>

                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center justify-between gap-2">
                                                                <h5 
                                                                    class="text-xs sm:text-sm font-bold tracking-tight"
                                                                    :class="done ? 'line-through text-slate-500 dark:text-zinc-400' : 'text-slate-900 dark:text-white'"
                                                                >
                                                                    {{ $step['title'] }}
                                                                </h5>
                                                                <button 
                                                                    type="button" 
                                                                    @click="done = !done" 
                                                                    class="text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded transition-colors shrink-0"
                                                                    :class="done ? 'text-emerald-700 bg-emerald-100 dark:bg-emerald-900 dark:text-emerald-200' : 'text-slate-500 hover:text-[#C8102E] bg-white dark:bg-zinc-700 border border-slate-200 dark:border-zinc-600'"
                                                                >
                                                                    <span x-show="!done">{{ $isGerman ? 'Erledigen' : 'Mark done' }}</span>
                                                                    <span x-show="done">{{ $isGerman ? 'Erledigt' : 'Done' }}</span>
                                                                </button>
                                                            </div>
                                                            @if(!empty($step['detail']))
                                                                <p 
                                                                    class="text-xs text-slate-600 dark:text-zinc-300 mt-1 leading-relaxed"
                                                                    :class="done ? 'opacity-70' : ''"
                                                                >
                                                                    {{ $step['detail'] }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                    @elseif($block['type'] === 'checklist')
                                        <div class="my-3 space-y-2">
                                            @if(!empty($block['heading']))
                                                <div class="flex items-center gap-2 pt-1 pb-0.5">
                                                    <span class="size-6 rounded-md bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                                        &check;
                                                    </span>
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                                        {{ $block['heading'] }}
                                                    </h4>
                                                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800 ml-auto">
                                                        {{ count($block['items']) }} {{ $isGerman ? 'Punkte' : 'Items' }}
                                                    </span>
                                                </div>
                                            @endif

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                @foreach($block['items'] as $item)
                                                    <label 
                                                        x-data="{ isChecked: {{ $item['checked'] ? 'true' : 'false' }} }"
                                                        class="flex items-start gap-2.5 p-3 rounded-xl border transition-all cursor-pointer shadow-2xs hover:shadow-xs"
                                                        :class="isChecked ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800' : 'bg-slate-50 dark:bg-zinc-800/70 border-slate-200 dark:border-zinc-700 hover:border-slate-300 dark:hover:border-zinc-600'"
                                                    >
                                                        <input 
                                                            type="checkbox" 
                                                            x-model="isChecked"
                                                            class="mt-0.5 size-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-zinc-600 shrink-0"
                                                        >
                                                        <div class="text-xs flex-1 min-w-0">
                                                            <span 
                                                                class="font-bold block text-slate-900 dark:text-white"
                                                                :class="isChecked ? 'line-through text-slate-500 dark:text-zinc-400' : ''"
                                                            >
                                                                {{ $item['title'] }}
                                                            </span>
                                                            @if(!empty($item['detail']))
                                                                <span 
                                                                    class="text-[11px] text-slate-500 dark:text-zinc-400 block mt-0.5 leading-snug"
                                                                    :class="isChecked ? 'opacity-70' : ''"
                                                                >
                                                                    {{ $item['detail'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                    @if(trim($msg['content']) !== '')
                                        <div class="prose prose-sm dark:prose-invert max-w-none leading-relaxed">
                                            {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                                        </div>
                                    @else
                                        <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 text-amber-800 dark:text-amber-200 text-xs sm:text-sm flex items-center justify-between gap-3">
                                            <span>{{ $isGerman ? 'Die Antwort konnte nicht geladen werden.' : 'The response could not be loaded.' }}</span>
                                        </div>
                                    @endif
                                @endforelse
                            </div>
                        @endif

                        {{-- Fallback Mode Badge if triggered --}}
                        @if(!empty($msg['is_fallback']))
                            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-zinc-800 flex items-center gap-1.5 text-xs text-amber-700 dark:text-amber-400 font-semibold">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $isGerman ? 'Antwort aus der offiziellen Bundes-Wissensdatenbank (Offline-Fallback)' : 'Verified guidance from Austrian Government Knowledge Base (Offline Fallback)' }}</span>
                            </div>
                        @endif

                        {{-- Official Source Citations --}}
                        @if(!empty($msg['sources']))
                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider block mb-1.5">
                                    {{ $isGerman ? 'Geprüfte offizielle Quellen der Republik Österreich:' : 'Verified Official Sources (Republic of Austria):' }}
                                </span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($msg['sources'] as $src)
                                        <a 
                                            href="{{ $src['url'] }}" 
                                            target="_blank" 
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700 transition-colors"
                                            title="{{ $src['description'] }}"
                                        >
                                            <span class="size-1.5 rounded-full bg-[#C8102E]"></span>
                                            <span class="font-medium">{{ $src['title'] }}</span>
                                            <span class="text-[10px] text-slate-500 dark:text-zinc-400 opacity-80">({{ $src['tag'] }})</span>
                                            <span class="text-[10px]">&nearr;</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Interactive Workflow Step Elements for Passport --}}
                        @if(!empty($msg['workflow']) && $msg['workflow'] === 'passport')
                            <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 space-y-3">
                                {{-- Step 1: Scenario Chooser --}}
                                @if($passportState['step'] === 1)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ $isGerman ? 'Schritt 1 von 3: Welcher Fall trifft zu?' : 'Step 1 of 3: What is your situation?' }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" wire:click="setPassportScenario('expiring')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs font-semibold">
                                                🛂 {{ $isGerman ? 'Reisepass läuft bald ab' : 'Passport expiring soon' }}
                                            </button>
                                            <button type="button" wire:click="setPassportScenario('expired_recently')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs font-semibold">
                                                ⏱️ {{ $isGerman ? 'Kürzlich abgelaufen (< 5 Jahre)' : 'Expired recently (< 5 years)' }}
                                            </button>
                                            <button type="button" wire:click="setPassportScenario('lost_stolen')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs font-semibold">
                                                ⚠️ {{ $isGerman ? 'Verloren oder gestohlen' : 'Lost or stolen' }}
                                            </button>
                                            <button type="button" wire:click="setPassportScenario('child')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs font-semibold">
                                                👶 {{ $isGerman ? 'Reisepass für ein Kind' : 'Passport for a child' }}
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 2: Urgency Chooser --}}
                                @if($passportState['step'] === 2)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ $isGerman ? 'Schritt 2 von 3: Gewünschte Zustell-Geschwindigkeit' : 'Step 2 of 3: Desired Delivery Speed' }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <button type="button" wire:click="setPassportUrgency('standard')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $isGerman ? 'Standard' : 'Standard' }} (75,90 €)</div>
                                                <div class="text-[11px] text-slate-500">ca. 5 Werktage</div>
                                            </button>
                                            <button type="button" wire:click="setPassportUrgency('express')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $isGerman ? 'Express' : 'Express' }} (100,00 €)</div>
                                                <div class="text-[11px] text-slate-500">2–3 Werktage</div>
                                            </button>
                                            <button type="button" wire:click="setPassportUrgency('one_day')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $isGerman ? '1-Tag Express' : '1-Day Express' }} (220,00 €)</div>
                                                <div class="text-[11px] text-slate-500">Nächster Werktag</div>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 3: Interactive Checklist with Checkbox State --}}
                                @if($passportState['step'] >= 3)
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                                {{ $isGerman ? 'Ihre interaktive Dokumenten-Checkliste:' : 'Your Interactive Document Checklist:' }}
                                            </label>
                                            <span class="text-xs font-bold text-[#C8102E]">
                                                {{ $passportFee['formatted'] }}
                                            </span>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($passportChecklist as $item)
                                                <label class="flex items-start gap-2.5 p-2 rounded-lg bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:click="togglePassportItem('{{ $item['id'] }}')"
                                                        {{ $item['checked'] ? 'checked' : '' }}
                                                        class="mt-1 size-4 rounded text-[#C8102E] focus:ring-[#C8102E]"
                                                    >
                                                    <div class="text-xs">
                                                        <span class="font-semibold block {{ $item['checked'] ? 'line-through opacity-70' : 'text-slate-900 dark:text-white' }}">
                                                            {{ $item['text'] }}
                                                        </span>
                                                        <span class="text-[11px] text-slate-500 dark:text-zinc-400 block">
                                                            {{ $item['detail'] }}
                                                        </span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        <div class="mt-3 pt-2.5 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 dark:border-zinc-700">
                                            <span class="text-xs text-slate-500">
                                                {{ $isGerman ? 'Zuständig: Jedes Magistratische Bezirksamt oder Bezirkshauptmannschaft' : 'Authority: Any Austrian passport office or district commission' }}
                                            </span>
                                            <button 
                                                type="button" 
                                                wire:click="generateDossier"
                                                class="px-3 py-1.5 rounded-lg bg-[#C8102E] text-white text-xs font-bold shadow-xs hover:bg-red-700 transition-colors"
                                            >
                                                {{ $isGerman ? 'Termin-Dossier erstellen' : 'Generate Appointment Dossier' }}
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Interactive Workflow Step Elements for Residence --}}
                        @if(!empty($msg['workflow']) && $msg['workflow'] === 'residence')
                            <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 space-y-3">
                                {{-- Step 1: Move Timeline --}}
                                @if($residenceState['step'] === 1)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ $isGerman ? 'Schritt 1 von 4: Zeitlicher Status Ihres Umzugs' : 'Step 1 of 4: Timeline of your relocation' }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <button type="button" wire:click="setResidenceMoveStatus('already_moved', 1)" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs font-semibold">
                                                ⏱️ {{ $isGerman ? 'Vor 1–3 Tagen eingezogen (In der Frist)' : 'Moved 1-3 days ago (On time)' }}
                                            </button>
                                            <button type="button" wire:click="setResidenceMoveStatus('already_moved', 7)" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs font-semibold">
                                                ⚠️ {{ $isGerman ? 'Vor mehr als 3 Tagen (Frist überschritten)' : 'Moved > 3 days ago (Overdue)' }}
                                            </button>
                                            <button type="button" wire:click="setResidenceMoveStatus('planning_to_move', 0)" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs font-semibold">
                                                📦 {{ $isGerman ? 'Ich plane den Umzug erst' : 'Planning future move' }}
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 2: Residence Type --}}
                                @if($residenceState['step'] === 2)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ $isGerman ? 'Schritt 2 von 4: Art des Wohnsitzes' : 'Step 2 of 4: Residence Category' }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" wire:click="setResidenceType('hauptwohnsitz')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $isGerman ? 'Hauptwohnsitz' : 'Primary Residence' }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $isGerman ? 'Mittelpunkt Ihrer Lebensbeziehungen (Familie/Arbeit)' : 'Center of vital personal and professional interests' }}</div>
                                            </button>
                                            <button type="button" wire:click="setResidenceType('nebenwohnsitz')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $isGerman ? 'Nebenwohnsitz' : 'Secondary Residence' }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $isGerman ? 'Zweitwohnung, Wochenendhaus oder Studentenzimmer' : 'Second home, student accommodation' }}</div>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 3: Housing Status (Rental vs Own) --}}
                                @if($residenceState['step'] === 3)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ $isGerman ? 'Schritt 3 von 4: Wohnverhältnis' : 'Step 3 of 4: Housing Type' }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" wire:click="setResidenceHousing('rented')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $isGerman ? 'Miete' : 'Rented' }}</div>
                                                <div class="text-[11px] text-amber-600 dark:text-amber-400 font-medium">{{ $isGerman ? 'Vermieter-Unterschrift zwingend erforderlich!' : 'Landlord signature legally mandatory' }}</div>
                                            </button>
                                            <button type="button" wire:click="setResidenceHousing('owner')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $isGerman ? 'Eigentum' : 'Owner-occupied' }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $isGerman ? 'Sie unterschreiben selbst als Unterkunftgeber' : 'You sign yourself as accommodation provider' }}</div>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 4: Channel Choice --}}
                                @if($residenceState['step'] === 4)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ $isGerman ? 'Schritt 4 von 4: Wie möchten Sie sich melden?' : 'Step 4 of 4: How would you like to register?' }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" wire:click="setResidenceChannel('id_austria')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">📱 {{ $isGerman ? 'Online mit ID Austria' : 'Online via ID Austria' }}</div>
                                                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">{{ $isGerman ? '100% digital ohne Wartezeit' : '100% digital without appointment' }}</div>
                                            </button>
                                            <button type="button" wire:click="setResidenceChannel('in_person')" class="p-2.5 text-left rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-[#C8102E] text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">🏛️ {{ $isGerman ? 'Vor Ort am Meldeamt' : 'In person at Meldeamt' }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $isGerman ? 'Gemeindeamt oder Bezirksamt in Wien' : 'Municipal registry or district commission' }}</div>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 5: Checklist --}}
                                @if($residenceState['step'] >= 5)
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                                {{ $isGerman ? 'Ihre Wohnsitz-Checkliste & Folgepflichten:' : 'Your Residence Checklist & Obligations:' }}
                                            </label>
                                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ $isGerman ? '0,00 € (Gebührenfrei)' : '€0.00 (Free)' }}
                                            </span>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($residenceChecklist as $item)
                                                <label class="flex items-start gap-2.5 p-2 rounded-lg bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:click="toggleResidenceItem('{{ $item['id'] }}')"
                                                        {{ $item['checked'] ? 'checked' : '' }}
                                                        class="mt-1 size-4 rounded text-[#C8102E] focus:ring-[#C8102E]"
                                                    >
                                                    <div class="text-xs">
                                                        <span class="font-semibold block {{ $item['checked'] ? 'line-through opacity-70' : 'text-slate-900 dark:text-white' }}">
                                                            {{ $item['text'] }}
                                                        </span>
                                                        <span class="text-[11px] text-slate-500 dark:text-zinc-400 block">
                                                            {{ $item['detail'] }}
                                                        </span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        <div class="mt-3 pt-2.5 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 dark:border-zinc-700">
                                            <span class="text-xs text-slate-500">
                                                {{ $isGerman ? 'Meldestellen: Jedes Gemeindeamt bzw. Wiener Bezirksamt' : 'Registration: Any town hall or Vienna district authority' }}
                                            </span>
                                            <button 
                                                type="button" 
                                                wire:click="generateDossier"
                                                class="px-3 py-1.5 rounded-lg bg-[#C8102E] text-white text-xs font-bold shadow-xs hover:bg-red-700 transition-colors"
                                            >
                                                {{ $isGerman ? 'Melde-Dossier erstellen' : 'Generate Registration Dossier' }}
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Interactive Next Steps & Follow-Up Action Buttons --}}
                        @if($msg['role'] === 'assistant' && !empty($nextSteps))
                            <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-zinc-800">
                                <div class="flex items-center gap-1.5 mb-2.5">
                                    <span class="flex size-5 items-center justify-center rounded-md bg-red-100 dark:bg-red-950/70 text-[#C8102E] dark:text-red-400 text-xs font-bold shrink-0">
                                        💡
                                    </span>
                                    <span class="text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wide">
                                        {{ $isGerman ? 'Empfohlene nächste Schritte & Folgefragen:' : 'Recommended next steps & questions:' }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($nextSteps as $step)
                                        <button 
                                            type="button" 
                                            wire:click="selectNextStep(@js($step))"
                                            class="group inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-red-50/80 dark:bg-red-950/40 hover:bg-[#C8102E] text-slate-800 dark:text-zinc-200 hover:text-white border border-red-200/80 dark:border-red-900/50 hover:border-[#C8102E] transition-all shadow-2xs hover:shadow-xs text-left active:scale-[0.98] cursor-pointer"
                                        >
                                            <span class="group-hover:translate-x-0.5 transition-transform">{{ $step }}</span>
                                            <span class="text-red-500 dark:text-red-400 group-hover:text-white transition-colors font-bold">&rarr;</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Action Toolbar for Assistant Messages (Listen, Copy) --}}
                        @if($msg['role'] === 'assistant')
                            <div class="mt-3 pt-2 flex items-center justify-end gap-2 text-xs text-slate-400 dark:text-zinc-500 opacity-90 group-hover:opacity-100 transition-opacity">
                                <button 
                                    type="button"
                                    @click="speak(@js($msg['content']))"
                                    class="inline-flex items-center gap-1 hover:text-slate-700 dark:hover:text-zinc-300 p-1 rounded"
                                    title="{{ $isGerman ? 'Vorlesen' : 'Read aloud' }}"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                    </svg>
                                    <span>{{ $isGerman ? 'Vorlesen' : 'Listen' }}</span>
                                </button>
                                <span>&bull;</span>
                                <button 
                                    type="button"
                                    @click="copyText(@js($msg['content']))"
                                    class="inline-flex items-center gap-1 hover:text-slate-700 dark:hover:text-zinc-300 p-1 rounded"
                                    title="{{ $isGerman ? 'Text kopieren' : 'Copy text' }}"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ $isGerman ? 'Kopieren' : 'Copy' }}</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Real-time Loading Card with Animated Loading Bar when waiting for DeepSeek --}}
            <div wire:loading.flex wire:target="sendMessage, selectSuggestedQuestion, selectNextStep" class="flex items-start gap-2.5 my-2">
                <div class="size-8 rounded-full bg-red-100 dark:bg-red-950 flex items-center justify-center text-[#C8102E] shrink-0 font-bold text-xs shadow-xs">
                    ÖS
                </div>
                <div class="p-4 sm:p-5 rounded-2xl rounded-tl-none bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm max-w-xl w-full flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-[#C8102E] animate-ping"></span>
                            <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                {{ $isGerman ? 'Offizielle Assistenz antwortet...' : 'Official Assistant is formulating response...' }}
                            </span>
                        </div>
                        <span class="text-[10px] font-semibold text-[#C8102E] uppercase tracking-wider bg-red-50 dark:bg-red-950 px-2 py-0.5 rounded border border-red-200 dark:border-red-900/60">
                            {{ $this->getModelDisplayName() }}
                        </span>
                    </div>

                    {{-- Real Animated Indeterminate Loading Bar --}}
                    <div class="w-full h-2 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden relative">
                        <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-red-600 via-[#C8102E] to-amber-500 rounded-full animate-indeterminate h-full"></div>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-zinc-400 leading-snug">
                        {{ $isGerman ? 'Geprüfte Rechtsvorschriften (Passgesetz, Meldegesetz) werden analysiert und in verständliche Karten übersetzt...' : 'Analyzing Austrian public service regulations and formatting plain-language cards...' }}
                    </p>

                    {{-- Placeholder skeleton steps --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1 opacity-60">
                        <div class="h-10 rounded-lg bg-slate-100 dark:bg-zinc-800 animate-pulse"></div>
                        <div class="h-10 rounded-lg bg-slate-100 dark:bg-zinc-800 animate-pulse"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Privacy & PII Notice Alert (if triggered) --}}
        @if($piiWarning)
            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-2.5">
                <svg class="size-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $piiWarning }}</span>
            </div>
        @endif

        {{-- Input and Suggestions Area --}}
        <div id="chat-input-area" class="sticky bottom-0 z-20 pt-2 pb-4 bg-gradient-to-t from-slate-50 dark:from-zinc-950 via-slate-50/90 dark:via-zinc-950/90 to-transparent">
            {{-- Suggested Questions Chips --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2.5 text-xs no-scrollbar" role="region" aria-label="{{ $isGerman ? 'Beispielfragen' : 'Suggested questions' }}">
                <span class="text-slate-400 dark:text-zinc-500 text-[11px] font-semibold uppercase tracking-wider shrink-0">
                    {{ $isGerman ? 'Vorschläge:' : 'Suggestions:' }}
                </span>
                <button 
                    type="button"
                    wire:click="selectSuggestedQuestion('{{ $isGerman ? 'Ich bin umgezogen. Muss ich das irgendwo melden?' : 'I moved to a new flat. Do I need to register this somewhere?' }}')"
                    class="px-3 py-1.5 rounded-full bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 hover:border-[#C8102E] text-slate-700 dark:text-zinc-300 whitespace-nowrap shadow-2xs hover:shadow-xs transition-all"
                >
                    🏠 {{ $isGerman ? 'Ich bin umgezogen. Was tun?' : 'I moved. What to do?' }}
                </button>
                <button 
                    type="button"
                    wire:click="selectSuggestedQuestion('{{ $isGerman ? 'Mein Reisepass läuft bald ab. Welche Unterlagen brauche ich?' : 'My passport expires soon. What documents do I need?' }}')"
                    class="px-3 py-1.5 rounded-full bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 hover:border-[#C8102E] text-slate-700 dark:text-zinc-300 whitespace-nowrap shadow-2xs hover:shadow-xs transition-all"
                >
                    🛂 {{ $isGerman ? 'Reisepass läuft ab – Unterlagen?' : 'Passport expires soon – documents?' }}
                </button>
                <button 
                    type="button"
                    wire:click="selectSuggestedQuestion('{{ $isGerman ? 'Ich habe einen unverständlichen Behördenbrief erhalten. Können Sie mir helfen?' : 'I received a confusing government letter. Can you explain it?' }}')"
                    class="px-3 py-1.5 rounded-full bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 hover:border-[#C8102E] text-slate-700 dark:text-zinc-300 whitespace-nowrap shadow-2xs hover:shadow-xs transition-all"
                >
                    ✉️ {{ $isGerman ? 'Unverständlichen Behördenbrief erklären' : 'Explain confusing government letter' }}
                </button>
                <button 
                    type="button"
                    wire:click="respondWithHumanSupport"
                    class="px-3 py-1.5 rounded-full bg-white dark:bg-zinc-900 border border-red-200 dark:border-red-900/60 hover:border-[#C8102E] text-[#C8102E] dark:text-red-400 whitespace-nowrap shadow-2xs hover:shadow-xs transition-all font-semibold"
                >
                    👤 {{ $isGerman ? 'Persönlicher Beamtentelefon-Kontakt' : 'Direct civil servant phone contact' }}
                </button>
            </div>

            {{-- Loading Progress Bar right above Input Box --}}
            <div wire:loading.flex wire:target="sendMessage, selectSuggestedQuestion, selectNextStep" class="w-full flex-col gap-1.5 mb-2 px-1">
                <div class="flex items-center justify-between text-xs font-semibold text-[#C8102E]">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin size-3.5 text-[#C8102E]" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ $isGerman ? 'Antwort wird von '.$this->getModelDisplayName().' generiert...' : 'Generating response via '.$this->getModelDisplayName().'...' }}</span>
                    </span>
                    <span class="text-[11px] text-slate-400 font-mono">{{ $isGerman ? 'Bitte kurz warten...' : 'Please wait...' }}</span>
                </div>
                <div class="w-full h-1.5 bg-slate-200 dark:bg-zinc-800 rounded-full overflow-hidden relative">
                    <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-red-600 via-[#C8102E] to-amber-500 rounded-full animate-indeterminate h-full"></div>
                </div>
            </div>

            {{-- Main Input Box Form --}}
            <form wire:submit.prevent="sendMessage" class="relative rounded-2xl bg-white dark:bg-zinc-900 border-2 border-slate-300 dark:border-zinc-700 focus-within:border-[#C8102E] dark:focus-within:border-[#C8102E] shadow-sm transition-all">
                <div class="p-2 sm:p-3 flex items-end gap-2">
                    <textarea 
                        wire:model="inputMessage"
                        placeholder="{{ $isGerman ? 'Fragen Sie in Ihren Worten (z. B. Reisepass verlängern, Meldezettel, ID Austria)...' : 'Ask in natural language (e.g. passport renewal, change of address, ID Austria)...' }}"
                        rows="2"
                        class="w-full bg-transparent resize-none border-0 focus:ring-0 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-zinc-500 py-1 px-2"
                        @keydown.enter.prevent="if(!$event.shiftKey) { $wire.sendMessage(); }"
                    ></textarea>

                    <button 
                        type="submit" 
                        class="px-4 py-2.5 rounded-xl bg-[#C8102E] hover:bg-red-700 text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 shrink-0 transition-colors shadow-xs disabled:opacity-50"
                        wire:loading.attr="disabled"
                        wire:target="sendMessage, selectSuggestedQuestion, selectNextStep"
                    >
                        <span wire:loading.remove wire:target="sendMessage, selectSuggestedQuestion, selectNextStep">{{ $isGerman ? 'Absenden' : 'Send' }}</span>
                        <span wire:loading.flex wire:target="sendMessage, selectSuggestedQuestion, selectNextStep" class="items-center gap-1.5">
                            <svg class="animate-spin size-3.5 text-white" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ $isGerman ? 'Wird geladen...' : 'Loading...' }}</span>
                        </span>
                        <svg wire:loading.remove wire:target="sendMessage, selectSuggestedQuestion, selectNextStep" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

                {{-- Privacy Protection & Security Subtext --}}
                <div class="px-4 py-2 bg-slate-50 dark:bg-zinc-950/70 border-t border-slate-100 dark:border-zinc-800 rounded-b-2xl flex flex-wrap items-center justify-between text-[11px] text-slate-500 dark:text-zinc-400 gap-2">
                    <div class="flex items-center gap-2">
                        <svg class="size-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $isGerman ? 'Automatischer Schutz persönlicher Daten (SVNr-Filter) • SSL-Verschlüsselung' : 'Automatic personal data redaction (SSN filter) • SSL Encrypted' }}</span>
                    </div>
                    <span>
                        {{ $isGerman ? 'Modell: '.$this->getModelDisplayName().' über OpenRouter' : 'Model: '.$this->getModelDisplayName().' via OpenRouter' }}
                    </span>
                </div>
            </form>
        </div>
    </main>

    {{-- Civil Servant Case Dossier Modal (Human Handover / Print / Copy) --}}
    @if($showDossierModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl flex flex-col gap-4">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="size-8 rounded-lg bg-red-100 dark:bg-red-950 text-[#C8102E] flex items-center justify-center font-bold">
                            📋
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                {{ $isGerman ? 'Bürger-Dossier für Beamtin / Beamten' : 'Citizen Case Dossier for Civil Servant' }}
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{ $isGerman ? 'Zur Vorlage am Amt oder zur telefonischen Vorbereitung' : 'For municipal appointment or telephone assistance' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDossierModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Dossier Preformatted Text --}}
                <div class="bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl p-4 font-mono text-xs text-slate-800 dark:text-zinc-200 max-h-96 overflow-y-auto whitespace-pre-wrap leading-relaxed select-all">
                    {{ $dossierContent }}
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <span class="text-xs text-slate-500 dark:text-zinc-400">
                        {{ $isGerman ? 'Keine sensiblen Daten enthalten • Datenschutzkonform' : 'Zero sensitive personal data included • GDPR compliant' }}
                    </span>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button"
                            @click="copyText(@js($dossierContent)); copiedDossier = true; setTimeout(() => copiedDossier = false, 2500);"
                            class="px-4 py-2 rounded-lg border border-slate-300 dark:border-zinc-700 hover:bg-slate-100 dark:hover:bg-zinc-800 text-xs font-bold transition-colors"
                        >
                            <span x-show="!copiedDossier">{{ $isGerman ? 'Dossier kopieren' : 'Copy Dossier' }}</span>
                            <span x-show="copiedDossier" class="text-emerald-600 font-bold">{{ $isGerman ? 'Kopiert!' : 'Copied!' }}</span>
                        </button>
                        <button 
                            type="button"
                            @click="window.print()"
                            class="px-4 py-2 rounded-lg bg-[#C8102E] hover:bg-red-700 text-white text-xs font-bold transition-colors shadow-xs"
                        >
                            {{ $isGerman ? 'Drucken / PDF' : 'Print / PDF' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
