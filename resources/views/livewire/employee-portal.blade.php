<div class="min-h-screen flex flex-col" wire:poll.3s>
    {{-- Official Federal Government Top Bar --}}
    <div class="bg-kp-navy text-white py-1.5 px-4 text-xs font-semibold tracking-wider flex flex-wrap items-center justify-between gap-2 border-b border-red-800 shadow-xs">
        <div class="flex items-center gap-2">
            <span class="inline-block size-2 rounded-full bg-white"></span>
            <span>REPUBLIK ÖSTERREICH • INTERNES BEAMTEN-PORTAL (VERTRAULICH / AMTLICH)</span>
        </div>
        <div class="flex items-center gap-3">
            @auth
                <span class="opacity-95 hidden sm:inline">👤 {{ auth()->user()->name }} ({{ auth()->user()->email }})</span>
                <span class="opacity-60 hidden sm:inline">|</span>
            @endauth
            <a href="{{ route('home') }}" class="hover:underline font-bold flex items-center gap-1 text-white">
                <span>&larr;</span>
                <span>Zurück zur Bürger-Assistenz</span>
            </a>
            @auth
                <span class="opacity-60">|</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hover:underline font-bold text-white cursor-pointer bg-red-800/80 hover:bg-red-900 px-2.5 py-0.5 rounded text-[11px] border border-red-700 transition-colors shadow-2xs">
                        Abmelden
                    </button>
                </form>
            @endauth
        </div>
    </div>

    {{-- Main Portal Navigation --}}
    <header class="bg-white dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 sticky top-0 z-20 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 flex items-center justify-center shrink-0">
                    <svg class="size-6 text-kp-textred" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L2 7v7c0 5.25 3.75 10.15 10 11.5 6.25-1.35 10-6.25 10-11.5V7L12 2zm0 3.3L19 9v5c0 4.1-2.9 7.9-7 9.1-4.1-1.2-7-5-7-9.1V9l7-3.7zM7 11h10v2H7v-2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-white">
                            Bundesservice Fall-Eskalation
                        </h1>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700">
                            Sachbearbeiter-Konsole
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400">
                        Übersicht aller Bürgergespräche, die an menschliche Beamtinnen und Beamte übergeben wurden.
                    </p>
                </div>
            </div>

            {{-- Metric Chips --}}
            <div class="flex items-center gap-2 text-xs">
                <div class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 flex items-center gap-1.5">
                    <span class="font-bold text-slate-700 dark:text-zinc-300">Gesamt:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $stats['total'] }}</span>
                </div>
                <div class="px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-900/60 flex items-center gap-1.5 text-kp-textred dark:text-red-300 font-bold">
                    <span class="size-2 rounded-full bg-kp-navy animate-pulse"></span>
                    <span>Offen:</span>
                    <span>{{ $stats['pending'] }}</span>
                </div>
                <div class="px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-900/60 flex items-center gap-1.5 text-blue-700 dark:text-blue-300 font-bold">
                    <span>In Bearbeitung:</span>
                    <span>{{ $stats['in_review'] }}</span>
                </div>
                <div class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900/60 flex items-center gap-1.5 text-emerald-700 dark:text-emerald-300 font-bold">
                    <span>Erledigt:</span>
                    <span>{{ $stats['resolved'] }}</span>
                </div>
            </div>
        </div>
    </header>

    {{-- Feedback alert if available --}}
    @if($feedbackMessage)
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-emerald-600"></span>
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" class="text-emerald-600 hover:text-emerald-900 text-sm font-bold">&times;</button>
            </div>
        </div>
    @endif

    {{-- Main Workspace --}}
    <main class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 flex-1 flex flex-col">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 flex-1">
            
            {{-- Left Column: Case List (5 cols on lg) --}}
            <div class="lg:col-span-5 flex flex-col gap-3">
                {{-- Filter & Search Header --}}
                <div class="p-3.5 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live.debounce.250ms="searchQuery" 
                            placeholder="Suche nach Fall-ID, Bürgeranliegen, Sachbearbeiter..."
                            class="w-full text-xs rounded-xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 px-3 py-2 pl-8 focus:ring-1 focus:ring-kp-navy text-slate-900 dark:text-white placeholder:text-slate-400"
                        >
                        <svg class="size-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Status Filter Buttons --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto text-xs pb-0.5 no-scrollbar">
                        <button 
                            type="button" 
                            wire:click="setStatusFilter('all')"
                            class="px-2.5 py-1 rounded-lg font-semibold transition-all {{ $statusFilter === 'all' ? 'bg-kp-navy text-white shadow-xs' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 hover:text-slate-900' }}"
                        >
                            Alle ({{ $stats['total'] }})
                        </button>
                        <button 
                            type="button" 
                            wire:click="setStatusFilter('pending_officer')"
                            class="px-2.5 py-1 rounded-lg font-semibold transition-all {{ $statusFilter === 'pending_officer' ? 'bg-kp-navy text-white shadow-xs' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 hover:text-slate-900' }}"
                        >
                            Offen ({{ $stats['pending'] }})
                        </button>
                        <button 
                            type="button" 
                            wire:click="setStatusFilter('in_review')"
                            class="px-2.5 py-1 rounded-lg font-semibold transition-all {{ $statusFilter === 'in_review' ? 'bg-kp-navy text-white shadow-xs' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 hover:text-slate-900' }}"
                        >
                            In Bearbeitung ({{ $stats['in_review'] }})
                        </button>
                        <button 
                            type="button" 
                            wire:click="setStatusFilter('resolved')"
                            class="px-2.5 py-1 rounded-lg font-semibold transition-all {{ $statusFilter === 'resolved' ? 'bg-kp-navy text-white shadow-xs' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 hover:text-slate-900' }}"
                        >
                            Erledigt ({{ $stats['resolved'] }})
                        </button>
                    </div>
                </div>

                {{-- Case Cards List --}}
                <div class="space-y-2.5 overflow-y-auto max-h-[calc(100vh-250px)] pr-1">
                    @forelse($cases as $case)
                        <button 
                            type="button" 
                            wire:click="selectConversation({{ $case->id }})"
                            class="w-full text-left p-4 rounded-2xl border transition-all shadow-2xs hover:shadow-xs relative {{ $selectedConversationId === $case->id ? 'bg-white dark:bg-zinc-900 border-kp-navy ring-1 ring-kp-navy' : 'bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 hover:border-slate-300' }}"
                        >
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">
                                        Fall #{{ $case->id }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 font-mono">
                                        ({{ $case->created_at?->format('d.m.Y H:i') }})
                                    </span>
                                </div>

                                {{-- Status Badge --}}
                                @if($case->status === 'pending_officer')
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-kp-textred border border-red-200 dark:bg-red-950 dark:border-red-900">
                                        Offen
                                    </span>
                                @elseif($case->status === 'in_review')
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950 dark:border-blue-900">
                                        In Prüfung
                                    </span>
                                @elseif($case->status === 'resolved')
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950 dark:border-emerald-900">
                                        Erledigt
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-zinc-200 line-clamp-2">
                                {{ $case->title ?: ($case->messages->firstWhere('role', 'user')['content'] ?? 'Bürgeranfrage ohne Titel') }}
                            </h3>

                            <div class="mt-2.5 flex items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-zinc-400">
                                <span class="flex items-center gap-1">
                                    <span>🌐</span>
                                    <span lang="{{ \App\Enums\Language::fromLocale($case->locale)->htmlLang() }}">{{ \App\Enums\Language::fromLocale($case->locale)->nativeName() }}</span>
                                    @if($case->active_workflow)
                                        <span class="ml-1 px-1.5 py-0.2 rounded bg-slate-100 dark:bg-zinc-800 text-[10px] font-bold">
                                            {{ $case->active_workflow }}
                                        </span>
                                    @endif
                                </span>
                                <span>{{ $case->messages->count() }} Nachrichten</span>
                            </div>

                            @if($case->assigned_officer)
                                <div class="mt-2 pt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between text-[11px] text-slate-600 dark:text-zinc-400 font-medium">
                                    <span class="flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-emerald-500"></span>
                                        <span>Betreut von: <strong>{{ $case->assigned_officer }}</strong></span>
                                    </span>
                                </div>
                            @else
                                <div class="mt-2 pt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between text-[11px]">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Wartet auf Sachbearbeiter</span>
                                    </span>
                                    <span 
                                        wire:click.stop="joinConversation({{ $case->id }})"
                                        class="px-2 py-0.5 rounded bg-kp-navy hover:bg-red-700 text-white font-bold text-[10px] cursor-pointer shadow-2xs transition-colors"
                                    >
                                        + Beitreten
                                    </span>
                                </div>
                            @endif
                        </button>
                    @empty
                        <div class="p-8 text-center rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800">
                            <span class="text-3xl block mb-2">✅</span>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-zinc-200">Keine eskalierten Fälle gefunden</h4>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                                Es liegen aktuell keine Bürgeranfragen in diesem Filter vor.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Right Column: Case Detail & Transcript View (7 cols on lg) --}}
            <div class="lg:col-span-7 flex flex-col gap-4">
                @if($selectedConversation)
                    {{-- Case Actions Top Bar --}}
                    <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-zinc-800 pb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                        Fall #{{ $selectedConversation->id }}
                                    </h2>
                                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $selectedConversation->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : ($selectedConversation->status === 'in_review' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $this->formatStatusLabel($selectedConversation->status) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Eskaliert am: {{ $selectedConversation->escalated_at?->format('d.m.Y H:i') ?? $selectedConversation->updated_at->format('d.m.Y H:i') }} Uhr
                                </p>
                            </div>

                            {{-- Action Buttons: Join, Status, Resolve --}}
                            <div class="flex items-center gap-2">
                                @if($this->isOfficerJoined($selectedConversation))
                                    <div class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Beigetreten ({{ $selectedConversation->assigned_officer }})</span>
                                    </div>
                                @else
                                    <button 
                                        type="button" 
                                        wire:click="joinConversation"
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-kp-navy hover:bg-red-700 text-white transition-colors shadow-2xs flex items-center gap-1.5 cursor-pointer"
                                        title="Dem Fall beitreten und Bürgerin/Bürger über den Beitritt informieren"
                                    >
                                        <span>👥</span>
                                        <span>Gespräch beitreten</span>
                                    </button>
                                @endif

                                <button 
                                    type="button" 
                                    wire:click="updateStatus('in_review')"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800 transition-colors"
                                >
                                    In Prüfung
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="resolveCase"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-2xs"
                                >
                                    &check; Als Erledigt abschließen
                                </button>
                            </div>
                        </div>

                        {{-- Escalation Reason Alert --}}
                        @if($selectedConversation->escalation_reason)
                            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                                <span class="font-bold shrink-0">⚠️ Eskalations-Grund:</span>
                                <span>{{ $selectedConversation->escalation_reason }}</span>
                            </div>
                        @endif

                        {{-- Assigned Officer & Notes Form --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="text-[11px] font-bold text-slate-500 block mb-1">
                                    Zuständige Beamtin / Beamter
                                </label>
                                <input 
                                    type="text" 
                                    wire:model="assignedOfficerInput"
                                    placeholder="z. B. Mag. Stefan Gruber (BKA)"
                                    class="w-full text-xs rounded-xl bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 px-3 py-2 text-slate-900 dark:text-white"
                                >
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-500 block mb-1">
                                    Interne Bearbeitungs-Notiz
                                </label>
                                <div class="flex gap-2">
                                    <input 
                                        type="text" 
                                        wire:model="officerNoteInput"
                                        placeholder="z. B. Bürger telefonisch kontaktiert, Termin vereinbart"
                                        class="w-full text-xs rounded-xl bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 px-3 py-2 text-slate-900 dark:text-white"
                                    >
                                    <button 
                                        type="button" 
                                        wire:click="saveOfficerNotes"
                                        class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shrink-0 transition-colors"
                                    >
                                        Speichern
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- AI Briefing Dossier Section --}}
                    @if($selectedConversation->case_dossier)
                        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-2">
                            <div class="flex items-center gap-2 border-b border-slate-100 dark:border-zinc-800 pb-2">
                                <span class="size-6 rounded-md bg-kp-navy text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    📋
                                </span>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                    Bürger-Dossier (Automatische AI-Fallzusammenfassung)
                                </h3>
                            </div>
                            <div class="prose prose-sm dark:prose-invert max-w-none text-xs leading-relaxed bg-slate-50 dark:bg-zinc-800/50 p-3 rounded-xl border border-slate-200 dark:border-zinc-700 font-mono">
                                {!! \Illuminate\Support\Str::markdown($selectedConversation->case_dossier) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Live Chat Transcript --}}
                    <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xs flex-1 flex flex-col space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="size-6 rounded-md bg-slate-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    💬
                                </span>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                    Vollständiges Chat-Protokoll (Bürger & KI)
                                </h3>
                            </div>
                            <span class="text-xs font-semibold text-slate-500">
                                {{ $selectedConversation->messages->count() }} Einträge
                            </span>
                        </div>

                        {{-- Scrollable Transcript --}}
                        <div class="space-y-3 overflow-y-auto max-h-[420px] p-2 bg-slate-50 dark:bg-zinc-950/50 rounded-xl border border-slate-200 dark:border-zinc-800">
                            @foreach($selectedConversation->messages as $msg)
                                <div class="flex flex-col {{ $msg->role === 'user' ? 'items-end' : 'items-start' }}">
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-1 px-1">
                                        <span class="font-bold {{ $msg->role === 'user' ? 'text-red-700 dark:text-red-400' : 'text-slate-600 dark:text-zinc-400' }}">
                                            {{ $msg->role === 'user' ? 'Bürgerin / Bürger' : ($msg->role === 'system' ? 'System-Hinweis' : 'KI-Assistenz') }}
                                        </span>
                                        <span>&bull;</span>
                                        <span>{{ $msg->created_at?->format('H:i:s') }}</span>
                                    </div>

                                    <div class="max-w-xl p-3 rounded-xl text-xs shadow-2xs {{ $msg->role === 'user' ? 'bg-kp-navy text-white rounded-tr-none' : ($msg->role === 'system' ? 'bg-blue-50 text-blue-900 border border-blue-200' : 'bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-slate-800 dark:text-zinc-200 rounded-tl-none') }}">
                                        {!! \App\Services\AustrianGov\MessageParser::markdown(\App\Services\AustrianGov\AnswerPresenter::plainText($msg->content)) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Officer Direct Response Form --}}
                        <div class="pt-2 border-t border-slate-100 dark:border-zinc-800">
                            <label class="text-[11px] font-bold text-slate-500 block mb-1">
                                ✉️ Amtliche Mitteilung direkt in das Bürger-Gespräch schreiben
                            </label>
                            <div class="flex gap-2">
                                <textarea 
                                    wire:model="officerResponseInput"
                                    rows="2"
                                    placeholder="Ihre verbindliche Antwort an die Bürgerin / den Bürger..."
                                    class="w-full text-xs rounded-xl bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 p-2 text-slate-900 dark:text-white"
                                ></textarea>
                                <button 
                                    type="button" 
                                    wire:click="sendOfficerMessage"
                                    class="px-4 py-2 bg-kp-navy hover:bg-red-700 text-white rounded-xl text-xs font-bold shrink-0 transition-colors shadow-2xs flex items-center gap-1.5"
                                >
                                    <span>Absenden</span>
                                    <span>&rarr;</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-12 text-center rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 my-auto">
                        <span class="text-4xl block mb-2">🏛️</span>
                        <h3 class="text-base font-bold text-slate-800 dark:text-zinc-200">Kein Fall ausgewählt</h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                            Wählen Sie einen Fall aus der linken Liste, um die Zusammenfassung, das Chat-Protokoll und die Sachbearbeiter-Optionen anzuzeigen.
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </main>
</div>
