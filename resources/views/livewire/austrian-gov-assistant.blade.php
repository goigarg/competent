<div
    lang="{{ $language->htmlLang() }}"
    dir="{{ $language->dir() }}"
    wire:poll.2s="checkForUpdates"
    class="min-h-screen flex flex-col {{ $highContrast ? 'bg-black text-white' : 'bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100' }} {{ $textSize === 'large' ? 'text-lg' : ($textSize === 'xlarge' ? 'text-xl' : 'text-base') }}"
    x-data="{
        copiedDossier: false,
        speak(text) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const cleanText = text.replace(/[#*`_\[\]]/g, '');
                const utterance = new SpeechSynthesisUtterance(cleanText);
                utterance.lang = @js($language->speechLang());
                window.speechSynthesis.speak(utterance);
            }
        },
        copyText(text) {
            navigator.clipboard.writeText(text);
        }
    }"
>
    {{-- Skip link for keyboard accessibility --}}
    <a href="#chat-input-area" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:start-2 focus:z-50 focus:p-3 focus:bg-kp-navy focus:text-white focus:rounded-md focus:shadow-lg">
        {{ __('chat.skip_directly_input_field') }}
    </a>

    {{-- Brand bar (Kompetent CI): logo left, web address in text red on the same baseline --}}
    <div class="{{ $highContrast ? 'bg-black border-zinc-700' : 'bg-white dark:bg-zinc-950 border-kp-bluegrey dark:border-zinc-800' }} border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-baseline justify-between gap-3">
            <x-kompetent-logo class="text-xl sm:text-2xl" :inverted="$highContrast" />
            <span class="text-sm sm:text-base text-kp-textred dark:text-kp-bluegrey">{{ config('app.brand_domain') }}</span>
        </div>
    </div>

    {{-- Fixed Top Loading Progress Bar when querying the model --}}
    <div wire:loading.flex wire:target="sendMessage, selectSuggestedQuestion, selectNextStep, confirmUnderstanding, rejectUnderstanding" class="fixed top-0 inset-x-0 z-50 h-1.5 bg-kp-bluegrey-30 dark:bg-zinc-800 overflow-hidden">
        <div class="h-full bg-kp-navy animate-indeterminate"></div>
    </div>

    {{-- Main Navigation & Accessibility Toolbar --}}
    <header class="sticky top-0 z-30 {{ $highContrast ? 'bg-zinc-900 border-zinc-700' : 'bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-slate-200 dark:border-zinc-800' }} border-b shadow-xs transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
            {{-- Brand / Title --}}
            <div class="flex items-center gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg font-light text-kp-black dark:text-white">
                            {{ __('chat.digital_administrative_assistant') }}
                        </h1>
                        {{-- AI disclosure from the first moment (CI chapter 08, AI Act Art. 50) --}}
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-kp-bluegrey-30 text-kp-black dark:bg-zinc-800 dark:text-zinc-200">
                            {{ __('chat.ai_assistant') }}
                        </span>
                    </div>
                    <p class="text-xs text-kp-muted dark:text-zinc-400 hidden sm:block">
                        {{ __('chat.i_am_ai_i_explain') }}
                    </p>
                </div>
            </div>

            {{-- Controls: Language Switcher, Accessibility & Human Support --}}
            <div class="flex items-center flex-wrap gap-2 sm:gap-3">
                {{-- Language switcher, generated from App\Enums\Language (CI: native names, no flags) --}}
                <label class="inline-flex items-center gap-2 text-sm">
                    <span class="sr-only">{{ __('chat.language_selection') }}</span>
                    <select
                        wire:change="setLocale($event.target.value)"
                        class="min-h-11 border-2 border-kp-navy bg-white px-3 text-sm font-semibold text-kp-navy dark:bg-zinc-900 dark:text-white dark:border-white cursor-pointer"
                        aria-label="{{ __('chat.language_selection') }}"
                    >
                        @foreach(\App\Enums\Language::cases() as $option)
                            <option value="{{ $option->value }}" lang="{{ $option->htmlLang() }}" dir="{{ $option->dir() }}" @selected($option === $language)>{{ $option->nativeName() }}</option>
                        @endforeach
                    </select>
                </label>

                {{-- Accessibility Controls Dropdown / Buttons --}}
                <div class="flex items-center gap-1 border border-slate-300 dark:border-zinc-700 rounded-lg p-1 bg-white dark:bg-zinc-800">
                    {{-- Text Size Cycle --}}
                    <button 
                        type="button"
                        wire:click="setTextSize('{{ $textSize === 'normal' ? 'large' : ($textSize === 'large' ? 'xlarge' : 'normal') }}')"
                        class="p-1.5 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-700 text-xs font-bold text-slate-700 dark:text-zinc-300"
                        title="{{ __('chat.adjust_text_size') }}"
                        aria-label="{{ __('chat.adjust_text_size') }}"
                    >
                        <span class="sr-only">{{ __('chat.text_size') }}</span>
                        @if($textSize === 'normal')
                            <span>A</span>
                        @elseif($textSize === 'large')
                            <span class="text-kp-textred font-extrabold">A+</span>
                        @else
                            <span class="text-kp-textred font-black">A++</span>
                        @endif
                    </button>

                    {{-- High Contrast Toggle --}}
                    <button 
                        type="button"
                        wire:click="toggleHighContrast"
                        class="p-1.5 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 {{ $highContrast ? 'bg-yellow-400 text-black' : '' }}"
                        title="{{ __('chat.toggle_high_contrast') }}"
                        aria-label="{{ __('chat.high_contrast') }}"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                </div>

                {{-- Human Assistance Button --}}
                <button 
                    type="button" 
                    wire:click="respondWithHumanSupport"
                    class="inline-flex items-center gap-1.5 px-3 min-h-11 border-2 border-kp-navy text-kp-navy bg-white hover:bg-kp-bluegrey-60 dark:bg-zinc-900 dark:text-white dark:border-white text-sm font-semibold transition-colors"
                >
                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>{{ __('chat.talk_person') }}</span>
                </button>

                {{-- Civil Servant Employee Portal Link --}}
                <a 
                    href="{{ route('portal.cases') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 dark:border-zinc-700 text-slate-700 dark:text-zinc-200 bg-slate-50 dark:bg-zinc-800 hover:bg-slate-100 dark:hover:bg-zinc-700 text-xs font-semibold transition-colors shadow-2xs"
                    title="{{ __('chat.civil_servant_portal_escalated_cases') }}"
                >
                    <span>🏛️</span>
                    <span class="hidden md:inline">{{ __('chat.officer_portal') }}</span>
                    @if($escalatedCasesCount > 0)
                        <span class="size-2 rounded-full bg-red-600 animate-pulse ms-0.5" title="{{ $escalatedCasesCount }} {{ __('chat.pending_cases') }}"></span>
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-red-100 dark:bg-red-950 text-kp-textred dark:text-red-300 border border-red-200 dark:border-red-900">{{ $escalatedCasesCount }}</span>
                    @endif
                </a>

                {{-- New Conversation Button --}}
                <button 
                    type="button" 
                    wire:click="startNewConversation"
                    wire:loading.attr="disabled"
                    wire:target="startNewConversation"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:text-kp-textred dark:hover:text-red-400 border border-slate-300 dark:border-zinc-700 hover:border-red-300 dark:hover:border-red-800 rounded-lg bg-white dark:bg-zinc-800 hover:bg-red-50/50 dark:hover:bg-red-950/30 transition-all cursor-pointer shadow-2xs disabled:opacity-50"
                    title="{{ __('chat.start_new_inquiry_existing_chats') }}"
                >
                    <span wire:loading.remove wire:target="startNewConversation" class="flex items-center gap-1.5">
                        <svg class="size-3.5 text-kp-textred" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('chat.new_chat') }}</span>
                    </span>
                    <span wire:loading.flex wire:target="startNewConversation" class="items-center gap-1.5">
                        <svg class="animate-spin size-3.5 text-kp-textred" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ __('chat.starting') }}</span>
                    </span>
                </button>
            </div>
        </div>
    </header>

    {{-- Main Content Layout --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col gap-6">

        {{-- Interactive Active Workflow Banner --}}
        @if($activeWorkflow)
            <div class="p-4 rounded-xl border border-red-200 dark:border-red-900/60 bg-red-50/80 dark:bg-red-950/30 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="size-3 rounded-full bg-kp-navy animate-ping"></span>
                    <div>
                        <span class="text-xs font-semibold text-kp-textred">
                            {{ __('chat.active_guided_workflow') }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ $activeWorkflow === 'passport' ? (__('chat.passport_issuance_workflow')) : (__('chat.residence_registration_workflow')) }}
                        </h3>
                    </div>
                </div>
                <button 
                    type="button" 
                    wire:click="resetWorkflow"
                    class="text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white underline underline-offset-2"
                >
                    {{ __('chat.exit_workflow') }}
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
                            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">
                                {{ __('chat.civil_servant_connected') }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm font-medium mt-0.5">
                            {{ __('chat.officer_now_handles_case', ['name' => $officerJoinedName ?: __('chat.official_caseworker')]) }}
                        </p>
                    </div>
                </div>
                <span class="hidden md:inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                    {{ __('chat.live_channel_active') }}
                </span>
            </div>
        @endif

        {{-- Conversational Stream Area --}}
        <div 
            class="flex-1 flex flex-col gap-4 overflow-y-auto"
            aria-live="polite"
            role="region"
            aria-label="{{ __('chat.conversation_stream') }}"
        >
            @foreach($messages as $msg)
                @php
                    $officerKind = \App\Services\AustrianGov\OfficerMessages::kind($msg);
                    $isOfficerMessage = $officerKind !== null;
                    // CI answer pattern: clarification or A steps · B documents · C sources · D next steps
                    $view = $msg['role'] === 'assistant' && ! $isOfficerMessage
                        ? \App\Services\AustrianGov\AnswerPresenter::present($msg, $locale, $loop->last)
                        : null;
                @endphp
                <div wire:key="msg-{{ $msg['id'] }}" class="flex flex-col gap-2 {{ $msg['role'] === 'user' ? 'items-end' : 'items-start' }}">
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-400 px-1">
                        @if($msg['role'] === 'user')
                            <span>{{ __('chat.citizen') }}</span>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @elseif($msg['role'] === 'system')
                            <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ __('chat.system_notice') }}</span>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @elseif($officerKind === 'joined')
                            <div class="flex items-center gap-1.5 font-bold text-emerald-700 dark:text-emerald-400">
                                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ __('chat.civil_servant_joined') }}</span>
                            </div>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @elseif($officerKind === 'message')
                            <div class="flex items-center gap-1.5 font-bold text-emerald-700 dark:text-emerald-400">
                                <span class="size-2 rounded-full bg-emerald-600"></span>
                                <span>{{ __('chat.civil_servant_message') }}</span>
                            </div>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @else
                            {{-- Every AI answer is labelled above the text, never hidden in small print (CI chapter 08) --}}
                            <div class="flex items-center gap-2">
                                <x-kompetent-flag class="h-2.5 w-[1rem] flex-none fill-kp-flag" />
                                <span class="font-normal tracking-[0.06em] text-kp-black dark:text-white">{{ config('app.brand') }}</span>
                                <span class="px-2 py-0.5 text-xs font-semibold border border-kp-navy text-kp-navy bg-white dark:bg-zinc-900 dark:text-white dark:border-white">{{ $view['label'] ?? (__('chat.ai_answer')) }}</span>
                            </div>
                            <span>&bull;</span>
                            <span>{{ $msg['timestamp'] }}</span>
                        @endif
                    </div>

                    {{-- Message Bubble --}}
                    <div class="max-w-3xl rounded-2xl p-4 sm:p-5 shadow-xs transition-all relative group {{ $msg['role'] === 'user' ? 'bg-kp-navy text-white rounded-se-none' : ($msg['role'] === 'system' ? 'bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 text-blue-900 dark:text-blue-200' : ($officerKind === 'joined' ? 'bg-emerald-50/90 dark:bg-emerald-950/50 border-2 border-emerald-400 dark:border-emerald-700 text-emerald-950 dark:text-emerald-100 rounded-ss-none' : ($officerKind === 'message' ? 'bg-amber-50/80 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-800 text-slate-800 dark:text-zinc-200 rounded-ss-none' : 'bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-slate-800 dark:text-zinc-200 rounded-ss-none'))) }}">
                        @php
                            $nextSteps = [];
                        @endphp

                        {{-- Message Content with Card-based Steps & Checklists --}}
                        @if($msg['role'] === 'user')
                            <div dir="auto" class="prose prose-sm text-white max-w-none leading-relaxed prose-p:my-1 prose-headings:text-white prose-strong:text-white prose-a:text-white prose-a:underline">
                                {!! \App\Services\AustrianGov\MessageParser::markdown($msg['content']) !!}
                            </div>
                        @elseif($msg['role'] === 'system')
                            <div dir="auto" class="prose prose-sm text-blue-900 dark:text-blue-200 max-w-none leading-relaxed">
                                {!! \App\Services\AustrianGov\MessageParser::markdown($msg['content']) !!}
                            </div>
                        @elseif($officerKind === 'joined')
                            <div class="flex items-start gap-3.5">
                                <div class="size-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg font-bold shrink-0 shadow-2xs">
                                    🏛️
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>{{ __('chat.official_join_recorded') }}</span>
                                    </div>
                                    <div class="text-xs sm:text-sm font-semibold leading-relaxed">
                                        {!! \App\Services\AustrianGov\MessageParser::markdown($msg['content']) !!}
                                    </div>
                                    <p class="text-[11px] text-emerald-700 dark:text-emerald-400">
                                        {{ __('chat.officer_actively_viewing_this_case') }}
                                    </p>
                                </div>
                            </div>
                        @elseif($view && $view['kind'] === 'clarify')
                            <x-answer.clarify :clarify="$view['clarify']" :message-id="$msg['id']" :awaiting="$view['awaiting_confirmation']" />
                        @else
                            <div class="space-y-3">
                                @forelse($view['blocks'] ?? [] as $block)
                                    @if($block['type'] === 'prose')
                                        <div dir="auto" class="prose prose-sm dark:prose-invert max-w-none leading-relaxed prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:font-bold prose-headings:mt-3 prose-headings:mb-1.5 prose-p:my-1.5 prose-ul:my-2 prose-li:my-0.5">
                                            {!! $block['html'] !!}
                                        </div>

                                    @elseif($block['type'] === 'steps')
                                        <x-answer.steps :block="$block" :message-id="$msg['id']" :sources="$view['sources']" />

                                    @elseif($block['type'] === 'checklist')
                                        <div class="my-3 space-y-2">
                                            @if(!empty($block['heading']))
                                                <div class="flex items-center gap-2 pt-1 pb-0.5">
                                                    <span class="size-6 rounded-md bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                                        &check;
                                                    </span>
                                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                                        {{ $block['heading'] }}
                                                    </h4>
                                                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800 ms-auto">
                                                        {{ count($block['items']) }} {{ __('chat.items') }}
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
                                        <div dir="auto" class="prose prose-sm dark:prose-invert max-w-none leading-relaxed">
                                            {!! \App\Services\AustrianGov\MessageParser::markdown(\App\Services\AustrianGov\AnswerPresenter::plainText($msg['content'])) !!}
                                        </div>
                                    @else
                                        <div class="p-3.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 text-amber-800 dark:text-amber-200 text-xs sm:text-sm flex items-center justify-between gap-3">
                                            <span>{{ __('chat.response_could_not_loaded') }}</span>
                                        </div>
                                    @endif
                                @endforelse
                            </div>
                        @endif

                        {{-- Fallback Mode Badge if triggered --}}
                        @if(!empty($msg['is_fallback']))
                            {{-- CI system state "not verified": ochre, always with a text label --}}
                            <div class="mt-3 border-s-4 border-kp-unverified bg-white dark:bg-zinc-900 px-4 py-3 flex flex-col gap-1.5 text-sm">
                                <span class="self-start px-2 py-0.5 text-xs font-semibold text-kp-black bg-kp-unverified-tint">{{ __('chat.not_verified') }}</span>
                                <span class="text-kp-black dark:text-zinc-200">{{ __('chat.this_information_does_not_come') }}</span>
                            </div>
                        @endif

                        @if($view && $view['kind'] === 'answer')
                            <x-answer.sources :cited="$view['cited_sources']" :further="$view['further_sources']" :message-id="$msg['id']" />
                        @endif

                        {{-- Interactive Workflow Step Elements for Passport --}}
                        @if(!empty($msg['workflow']) && $msg['workflow'] === 'passport')
                            <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 space-y-3">
                                {{-- Step 1: Scenario Chooser --}}
                                @if($passportState['step'] === 1)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ __('chat.step_1_3_what_situation') }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" wire:click="setPassportScenario('expiring')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs font-semibold">
                                                🛂 {{ __('chat.passport_expiring_soon') }}
                                            </button>
                                            <button type="button" wire:click="setPassportScenario('expired_recently')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs font-semibold">
                                                ⏱️ {{ __('chat.expired_recently_5_years') }}
                                            </button>
                                            <button type="button" wire:click="setPassportScenario('lost_stolen')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs font-semibold">
                                                ⚠️ {{ __('chat.lost_stolen') }}
                                            </button>
                                            <button type="button" wire:click="setPassportScenario('child')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs font-semibold">
                                                👶 {{ __('chat.passport_child') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 2: Urgency Chooser --}}
                                @if($passportState['step'] === 2)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ __('chat.step_2_3_desired_delivery') }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <button type="button" wire:click="setPassportUrgency('standard')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ __('chat.standard') }} (75,90 €)</div>
                                                <div class="text-[11px] text-slate-500">ca. 5 Werktage</div>
                                            </button>
                                            <button type="button" wire:click="setPassportUrgency('express')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ __('chat.express') }} (100,00 €)</div>
                                                <div class="text-[11px] text-slate-500">2–3 Werktage</div>
                                            </button>
                                            <button type="button" wire:click="setPassportUrgency('one_day')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ __('chat.1_day_express') }} (220,00 €)</div>
                                                <div class="text-[11px] text-slate-500">Nächster Werktag</div>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 3: Interactive Checklist with Checkbox State --}}
                                @if($passportState['step'] >= 3)
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-xs font-bold text-slate-900 dark:text-white">
                                                {{ __('chat.interactive_document_checklist') }}
                                            </label>
                                            <span class="text-xs font-bold text-kp-textred">
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
                                                        class="mt-1 size-4 rounded text-kp-textred focus:ring-kp-navy"
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
                                                {{ __('chat.authority_any_austrian_passport_office') }}
                                            </span>
                                            <button 
                                                type="button" 
                                                wire:click="generateDossier"
                                                class="px-3 py-1.5 rounded-lg bg-kp-navy text-white text-xs font-bold shadow-xs hover:bg-red-700 transition-colors"
                                            >
                                                {{ __('chat.generate_appointment_dossier') }}
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
                                            {{ __('chat.step_1_4_timeline_relocation') }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <button type="button" wire:click="setResidenceMoveStatus('already_moved', 1)" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs font-semibold">
                                                ⏱️ {{ __('chat.moved_1_3_days_ago') }}
                                            </button>
                                            <button type="button" wire:click="setResidenceMoveStatus('already_moved', 7)" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs font-semibold">
                                                ⚠️ {{ __('chat.moved_3_days_ago_overdue') }}
                                            </button>
                                            <button type="button" wire:click="setResidenceMoveStatus('planning_to_move', 0)" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs font-semibold">
                                                📦 {{ __('chat.planning_future_move') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 2: Residence Type --}}
                                @if($residenceState['step'] === 2)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ __('chat.step_2_4_residence_category') }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" wire:click="setResidenceType('hauptwohnsitz')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ __('chat.primary_residence') }}</div>
                                                <div class="text-[11px] text-slate-500">{{ __('chat.center_vital_personal_professional_interests') }}</div>
                                            </button>
                                            <button type="button" wire:click="setResidenceType('nebenwohnsitz')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ __('chat.secondary_residence') }}</div>
                                                <div class="text-[11px] text-slate-500">{{ __('chat.second_home_student_accommodation') }}</div>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 3: Housing Status (Rental vs Own) --}}
                                @if($residenceState['step'] === 3)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ __('chat.step_3_4_housing_type') }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" wire:click="setResidenceHousing('rented')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ __('chat.rented') }}</div>
                                                <div class="text-[11px] text-amber-600 dark:text-amber-400 font-medium">{{ __('chat.landlord_signature_legally_mandatory') }}</div>
                                            </button>
                                            <button type="button" wire:click="setResidenceHousing('owner')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">{{ __('chat.owner_occupied') }}</div>
                                                <div class="text-[11px] text-slate-500">{{ __('chat.you_sign_yourself_as_accommodation') }}</div>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 4: Channel Choice --}}
                                @if($residenceState['step'] === 4)
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 block">
                                            {{ __('chat.step_4_4_how_would') }}
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <button type="button" wire:click="setResidenceChannel('id_austria')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">📱 {{ __('chat.online_id_austria') }}</div>
                                                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">{{ __('chat.100_digital_without_appointment') }}</div>
                                            </button>
                                            <button type="button" wire:click="setResidenceChannel('in_person')" class="p-2.5 text-start rounded-lg border border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 hover:border-kp-navy text-xs">
                                                <div class="font-bold text-slate-900 dark:text-white">🏛️ {{ __('chat.person_meldeamt') }}</div>
                                                <div class="text-[11px] text-slate-500">{{ __('chat.municipal_registry_district_commission') }}</div>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Step 5: Checklist --}}
                                @if($residenceState['step'] >= 5)
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-xs font-bold text-slate-900 dark:text-white">
                                                {{ __('chat.residence_checklist_obligations') }}
                                            </label>
                                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ __('chat.0_00_free') }}
                                            </span>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($residenceChecklist as $item)
                                                <label class="flex items-start gap-2.5 p-2 rounded-lg bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:click="toggleResidenceItem('{{ $item['id'] }}')"
                                                        {{ $item['checked'] ? 'checked' : '' }}
                                                        class="mt-1 size-4 rounded text-kp-textred focus:ring-kp-navy"
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
                                                {{ __('chat.registration_any_town_hall_vienna') }}
                                            </span>
                                            <button 
                                                type="button" 
                                                wire:click="generateDossier"
                                                class="px-3 py-1.5 rounded-lg bg-kp-navy text-white text-xs font-bold shadow-xs hover:bg-red-700 transition-colors"
                                            >
                                                {{ __('chat.generate_registration_dossier') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- CI answer block D; "Mit einer Person sprechen" is always the last option --}}
                        @if($view && $view['kind'] === 'answer')
                            <x-answer.next-steps :steps="$view['next_steps']" />
                        @endif

                        {{-- Action Toolbar for Assistant Messages (Listen, Copy) --}}
                        @if($msg['role'] === 'assistant')
                            <div class="mt-3 pt-2 flex items-center justify-end gap-2 text-xs text-slate-400 dark:text-zinc-500 opacity-90 group-hover:opacity-100 transition-opacity">
                                <button 
                                    type="button"
                                    @click="speak(@js($view['plain_text'] ?? $msg['content']))"
                                    class="inline-flex items-center gap-1 hover:text-slate-700 dark:hover:text-zinc-300 p-1 rounded"
                                    title="{{ __('chat.read_aloud') }}"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                    </svg>
                                    <span>{{ __('chat.listen') }}</span>
                                </button>
                                <span>&bull;</span>
                                <button 
                                    type="button"
                                    @click="copyText(@js($view['plain_text'] ?? $msg['content']))"
                                    class="inline-flex items-center gap-1 hover:text-slate-700 dark:hover:text-zinc-300 p-1 rounded"
                                    title="{{ __('chat.copy_text') }}"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ __('chat.copy') }}</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Real-time Loading Card with Animated Loading Bar when waiting for DeepSeek --}}
            <div wire:loading.flex wire:target="sendMessage, selectSuggestedQuestion, selectNextStep, confirmUnderstanding, rejectUnderstanding" class="flex items-start gap-2.5 my-2">
                <div class="size-8 rounded-full bg-red-100 dark:bg-red-950 flex items-center justify-center text-kp-textred shrink-0 font-bold text-xs shadow-xs">
                    ÖS
                </div>
                <div class="p-4 sm:p-5 rounded-2xl rounded-ss-none bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm max-w-xl w-full flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-kp-navy animate-ping"></span>
                            <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                {{ __('chat.official_assistant_formulating_response') }}
                            </span>
                        </div>
                        <span class="text-[10px] font-semibold text-kp-textred bg-red-50 dark:bg-red-950 px-2 py-0.5 rounded border border-red-200 dark:border-red-900/60">
                            {{ $this->getModelDisplayName() }}
                        </span>
                    </div>

                    {{-- Real Animated Indeterminate Loading Bar --}}
                    <div class="w-full h-2 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden relative">
                        <div class="absolute inset-y-0 start-0 bg-kp-navy animate-indeterminate h-full"></div>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-zinc-400 leading-snug">
                        {{ __('chat.analyzing_austrian_public_service_regulations') }}
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
            {{-- Loading Progress Bar right above Input Box --}}
            <div wire:loading.flex wire:target="sendMessage, selectSuggestedQuestion, selectNextStep, confirmUnderstanding, rejectUnderstanding" class="w-full flex-col gap-1.5 mb-2 px-1">
                <div class="flex items-center justify-between text-xs font-semibold text-kp-textred">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin size-3.5 text-kp-textred" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ __('chat.generating_response_via', ['model' => $this->getModelDisplayName()]) }}</span>
                    </span>
                    <span class="text-[11px] text-slate-400 font-mono">{{ __('chat.please_wait') }}</span>
                </div>
                <div class="w-full h-1.5 bg-slate-200 dark:bg-zinc-800 rounded-full overflow-hidden relative">
                    <div class="absolute inset-y-0 start-0 bg-kp-navy animate-indeterminate h-full"></div>
                </div>
            </div>

            {{-- Main Input Box Form --}}
            <form wire:submit.prevent="sendMessage" class="relative rounded-2xl bg-white dark:bg-zinc-900 border-2 border-slate-300 dark:border-zinc-700 focus-within:border-kp-navy dark:focus-within:border-kp-navy shadow-sm transition-all">
                <div class="p-2 sm:p-3 flex items-end gap-2">
                    <textarea 
                        wire:model="inputMessage"
                        placeholder="{{ __('chat.ask_natural_language_e_g') }}"
                        rows="2"
                        class="w-full bg-transparent resize-none border-0 focus:ring-0 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-zinc-500 py-1 px-2"
                        @keydown.enter.prevent="if(!$event.shiftKey) { $wire.sendMessage(); }"
                    ></textarea>

                    <button 
                        type="submit" 
                        class="px-4 py-2.5 rounded-xl bg-kp-navy hover:bg-red-700 text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 shrink-0 transition-colors shadow-xs disabled:opacity-50"
                        wire:loading.attr="disabled"
                        wire:target="sendMessage, selectSuggestedQuestion, selectNextStep, confirmUnderstanding, rejectUnderstanding"
                    >
                        <span wire:loading.remove wire:target="sendMessage, selectSuggestedQuestion, selectNextStep, confirmUnderstanding, rejectUnderstanding">{{ __('chat.send') }}</span>
                        <span wire:loading.flex wire:target="sendMessage, selectSuggestedQuestion, selectNextStep, confirmUnderstanding, rejectUnderstanding" class="items-center gap-1.5">
                            <svg class="animate-spin size-3.5 text-white" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ __('chat.loading') }}</span>
                        </span>
                        <svg wire:loading.remove wire:target="sendMessage, selectSuggestedQuestion, selectNextStep, confirmUnderstanding, rejectUnderstanding" class="size-4 rtl:-scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        <span>{{ __('chat.automatic_personal_data_redaction_ssn') }}</span>
                    </div>
                    <span>
                        {{ __('chat.model_via_openrouter', ['model' => $this->getModelDisplayName()]) }}
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
                        <div class="size-8 rounded-lg bg-red-100 dark:bg-red-950 text-kp-textred flex items-center justify-center font-bold">
                            📋
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                {{ __('chat.citizen_case_dossier_civil_servant') }}
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{ __('chat.municipal_appointment_telephone_assistance') }}
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
                        {{ __('chat.zero_sensitive_personal_data_included') }}
                    </span>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button"
                            @click="copyText(@js($dossierContent)); copiedDossier = true; setTimeout(() => copiedDossier = false, 2500);"
                            class="px-4 py-2 rounded-lg border border-slate-300 dark:border-zinc-700 hover:bg-slate-100 dark:hover:bg-zinc-800 text-xs font-bold transition-colors"
                        >
                            <span x-show="!copiedDossier">{{ __('chat.copy_dossier') }}</span>
                            <span x-show="copiedDossier" class="text-emerald-600 font-bold">{{ __('chat.copied') }}</span>
                        </button>
                        <button 
                            type="button"
                            @click="window.print()"
                            class="px-4 py-2 rounded-lg bg-kp-navy hover:bg-red-700 text-white text-xs font-bold transition-colors shadow-xs"
                        >
                            {{ __('chat.print_pdf') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
