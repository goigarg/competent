<?php

namespace App\Livewire;

use App\Enums\Language;
use App\Livewire\Concerns\AppliesLanguage;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\KnowledgeNugget;
use App\Services\Ai\Contracts\AiAssistantInterface;
use App\Services\AustrianGov\IntentMatcher;
use App\Services\AustrianGov\KnowledgeBaseService;
use App\Services\AustrianGov\KnowledgeDistiller;
use App\Services\AustrianGov\MessageParser;
use App\Services\AustrianGov\OfficerMessages;
use App\Services\AustrianGov\Workflows\PassportWorkflow;
use App\Services\AustrianGov\Workflows\ResidenceWorkflow;
use App\Services\Citations\CitationMarkers;
use App\Services\Citations\CitedSource;
use App\Services\Citations\Contracts\CitationResolver;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @phpstan-type ChatMessage array{
 *     id: string,
 *     role: string,
 *     content: string,
 *     timestamp: string,
 *     sources?: array<int, array<string, mixed>>,
 *     workflow?: ?string,
 *     is_fallback?: bool,
 *     checklist?: array<int, string>|null,
 *     is_human_request?: bool
 * }
 * @phpstan-type NewChatMessage array{
 *     role: string,
 *     content: string,
 *     id?: string,
 *     timestamp?: string,
 *     sources?: array<int, array<string, mixed>>,
 *     workflow?: ?string,
 *     is_fallback?: bool,
 *     checklist?: array<int, string>|null,
 *     is_human_request?: bool
 * }
 */
class AustrianGovAssistant extends Component
{
    use AppliesLanguage;

    public ?int $conversationId = null;

    public int $escalatedCasesCount = 0;

    public string $locale = 'de';

    /**
     * @var list<ChatMessage>
     */
    public array $messages = [];

    public string $inputMessage = '';

    public bool $isLoading = false;

    public ?string $piiWarning = null;

    public bool $isOfficerJoined = false;

    public ?string $officerJoinedName = null;

    public ?string $activeWorkflow = null; // 'passport', 'residence', or null

    /**
     * @var array{
     *     step: int,
     *     scenario: ?string,
     *     urgency: ?string,
     *     age_group: ?string,
     *     checked_items: array<string, bool>,
     *     authority_location: ?string
     * }
     */
    public array $passportState = PassportWorkflow::DEFAULT_STATE;

    /**
     * @var array{
     *     step: int,
     *     move_status: ?string,
     *     days_since_move: int,
     *     residence_type: string,
     *     housing_type: string,
     *     channel: string,
     *     checked_items: array<string, bool>
     * }
     */
    public array $residenceState = ResidenceWorkflow::DEFAULT_STATE;

    public bool $showDossierModal = false;

    public string $dossierContent = '';

    public string $textSize = 'normal'; // 'normal', 'large', 'xlarge'

    public bool $highContrast = false;

    public function mount(KnowledgeBaseService $kbService): void
    {
        $this->locale = Language::fromLocale(is_string(session('locale')) ? session('locale') : null)->value;
        $this->applyLanguage();
        $this->loadOrCreateConversation();
        $this->applyLanguage();
    }

    protected function loadOrCreateConversation(): void
    {
        try {
            $sessionConvId = session('austrian_gov_conversation_id');
            $conversation = is_numeric($sessionConvId)
                ? Conversation::with('messages')->find((int) $sessionConvId)
                : null;

            if (! $conversation) {
                $conversation = Conversation::with('messages')
                    ->where('session_id', session()->getId())
                    ->where('status', 'active')
                    ->latest('updated_at')
                    ->first();
            }

            if ($conversation) {
                $this->conversationId = $conversation->id;
                session(['austrian_gov_conversation_id' => $conversation->id]);
                $this->locale = Language::fromLocale($conversation->locale)->value;
                $this->applyLanguage();
                $this->activeWorkflow = $conversation->active_workflow;

                if (! empty($conversation->workflow_data)) {
                    $this->passportState = PassportWorkflow::normalizeState($conversation->workflow_data['passportState'] ?? []);
                    $this->residenceState = ResidenceWorkflow::normalizeState($conversation->workflow_data['residenceState'] ?? []);
                }

                if (! empty($conversation->assigned_officer)) {
                    $this->isOfficerJoined = true;
                    $this->officerJoinedName = $conversation->assigned_officer;
                }

                if ($conversation->messages->isNotEmpty()) {
                    $lastUserText = null;
                    $repairedMessages = [];

                    foreach ($conversation->messages as $m) {
                        $content = (string) $m->content;
                        if (OfficerMessages::kind(['workflow' => $m->workflow, 'content' => $content]) === 'joined') {
                            $this->isOfficerJoined = true;
                            if (! empty($conversation->assigned_officer)) {
                                $this->officerJoinedName = $conversation->assigned_officer;
                            }
                        }

                        if ($m->role === 'user') {
                            $lastUserText = $content;
                            $repairedMessages[] = $this->messageFromModel($m);
                        } elseif ($m->role === 'assistant') {
                            if (trim($content) === '') {
                                $fallback = app(KnowledgeBaseService::class)->getFallbackResponse($lastUserText ?? 'general', $this->locale);
                                $m->update(['content' => $fallback['content'], 'is_fallback' => true]);
                            }

                            $repairedMessages[] = $this->messageFromModel($m);
                        }
                    }

                    $this->messages = $repairedMessages;
                    $this->updateEscalatedCount();

                    return;
                }
            } else {
                $conversation = Conversation::create([
                    'session_id' => session()->getId(),
                    'locale' => $this->locale,
                    'status' => 'active',
                ]);

                $this->conversationId = $conversation->id;
                session(['austrian_gov_conversation_id' => $conversation->id]);
            }
        } catch (\Throwable) {
            // Memory or testing fallback
        }

        $this->initWelcomeMessage();
        if (! empty($this->messages[0])) {
            $this->persistMessage($this->messages[0]);
        }
        $this->updateEscalatedCount();
    }

    public function startNewConversation(): void
    {
        if ($this->conversationId) {
            try {
                // If previous conversation was not escalated to a human officer, mark it completed
                Conversation::where('id', $this->conversationId)
                    ->where('is_escalated', false)
                    ->update(['status' => 'completed']);
            } catch (\Throwable) {
            }
        }

        $this->isOfficerJoined = false;
        $this->officerJoinedName = null;
        $this->activeWorkflow = null;
        $this->passportState = PassportWorkflow::DEFAULT_STATE;
        $this->residenceState = ResidenceWorkflow::DEFAULT_STATE;
        $this->inputMessage = '';
        $this->messages = [];
        $this->showDossierModal = false;
        $this->dossierContent = '';

        try {
            $conversation = Conversation::create([
                'session_id' => session()->getId(),
                'locale' => $this->locale,
                'status' => 'active',
            ]);

            $this->conversationId = $conversation->id;
            session(['austrian_gov_conversation_id' => $conversation->id]);
        } catch (\Throwable) {
            $this->conversationId = null;
        }

        $this->initWelcomeMessage();
        if (! empty($this->messages[0])) {
            $this->persistMessage($this->messages[0]);
        }
        $this->updateEscalatedCount();
    }

    /**
     * @param  ChatMessage  $msgData
     */
    protected function persistMessage(array $msgData): void
    {
        if (! $this->conversationId) {
            return;
        }

        try {
            ConversationMessage::create([
                'conversation_id' => $this->conversationId,
                'uuid' => $msgData['id'],
                'role' => $msgData['role'],
                'content' => $msgData['content'],
                'sources' => $msgData['sources'] ?? null,
                'is_fallback' => $msgData['is_fallback'] ?? false,
                'workflow' => $msgData['workflow'] ?? $this->activeWorkflow,
            ]);

            $conv = Conversation::find($this->conversationId);
            if ($conv) {
                $updates = [
                    'active_workflow' => $this->activeWorkflow,
                    'locale' => $this->locale,
                    'workflow_data' => [
                        'passportState' => $this->passportState,
                        'residenceState' => $this->residenceState,
                    ],
                ];

                if (! $conv->title && $msgData['role'] === 'user') {
                    $updates['title'] = Str::limit(trim(strip_tags($msgData['content'])), 60);
                }

                $conv->update($updates);
            }
        } catch (\Throwable) {
        }
    }

    /**
     * @param  NewChatMessage  $msg
     * @return ChatMessage
     */
    protected function addMessage(array $msg): array
    {
        $message = [
            ...$msg,
            'id' => empty($msg['id']) ? (string) Str::uuid() : $msg['id'],
            'timestamp' => empty($msg['timestamp']) ? now()->format('H:i') : $msg['timestamp'],
        ];

        $this->messages[] = $message;
        $this->persistMessage($message);

        return $message;
    }

    /**
     * @return ChatMessage
     */
    protected function messageFromModel(ConversationMessage $m): array
    {
        return [
            'id' => $m->uuid ?? (string) $m->id,
            'role' => $m->role,
            'content' => $m->content,
            'timestamp' => $m->created_at?->format('H:i') ?? now()->format('H:i'),
            'sources' => $m->sources ?? [],
            'workflow' => $m->workflow,
            'is_fallback' => $m->is_fallback,
        ];
    }

    protected function markConversationAsEscalated(string $reason): void
    {
        if (! $this->conversationId) {
            return;
        }

        try {
            $conv = Conversation::find($this->conversationId);
            if ($conv) {
                $dossier = $conv->case_dossier ?: $this->buildEscalationDossier();

                $conv->update([
                    'is_escalated' => true,
                    'escalated_at' => now(),
                    'escalation_reason' => $reason,
                    'status' => 'pending_officer',
                    'case_dossier' => $dossier,
                ]);

                $this->updateEscalatedCount();
            }
        } catch (\Throwable) {
        }
    }

    /**
     * Case summary in the given language (citizen: their language; staff portal: always German).
     */
    protected function buildEscalationDossier(?string $locale = null): string
    {
        $locale ??= $this->locale;

        if ($this->activeWorkflow === 'passport') {
            return (new PassportWorkflow($this->passportState))->generateDossierSummary($locale);
        }

        if ($this->activeWorkflow === 'residence') {
            return (new ResidenceWorkflow($this->residenceState))->generateDossierSummary($locale);
        }

        $userMsgs = collect($this->messages)
            ->where('role', 'user')
            ->pluck('content')
            ->implode("\n* ");

        return $this->t('assistant.dossier_general', [
            'datetime' => now()->format('d.m.Y H:i'),
            'requests' => $userMsgs ?: $this->t('assistant.dossier_general_empty', [], $locale),
        ], $locale);
    }

    protected function updateEscalatedCount(): void
    {
        try {
            $this->escalatedCasesCount = Conversation::escalated()
                ->where('status', '!=', 'resolved')
                ->count();
        } catch (\Throwable) {
            $this->escalatedCasesCount = 0;
        }
    }

    public function setLocale(string $locale): void
    {
        $language = Language::tryFrom($locale);
        if ($language === null) {
            return;
        }

        $this->locale = $language->value;
        session(['locale' => $language->value]);
        $this->applyLanguage();

        if ($this->conversationId) {
            try {
                Conversation::where('id', $this->conversationId)->update(['locale' => $language->value]);
            } catch (\Throwable) {
            }
        }

        // Add notification message in conversation
        $this->addMessage([
            'role' => 'system',
            'content' => $this->t('chat.language_switched'),
        ]);

        $title = config('app.brand').' · '.$this->t('chat.digital_administrative_assistant');
        $this->js('document.documentElement.lang = '.json_encode($language->htmlLang()).'; document.documentElement.dir = '.json_encode($language->dir()).'; document.title = '.json_encode($title).';');
    }

    public function setTextSize(string $size): void
    {
        if (in_array($size, ['normal', 'large', 'xlarge'])) {
            $this->textSize = $size;
        }
    }

    public function toggleHighContrast(): void
    {
        $this->highContrast = ! $this->highContrast;
    }

    protected function initWelcomeMessage(): void
    {
        $this->messages = [
            [
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $this->withContentFallbackNotice($this->t('assistant.welcome')),
                'timestamp' => now()->format('H:i'),
                'sources' => app(KnowledgeBaseService::class)->getOfficialSources('general', $this->locale),
            ],
        ];
    }

    public function selectSuggestedQuestion(string $question): void
    {
        $this->inputMessage = $question;
        $this->sendMessage();
    }

    public function selectNextStep(string $action): void
    {
        $action = trim($action);
        if ($action === '') {
            return;
        }

        $intents = app(IntentMatcher::class);

        if ($intents->wantsDossier($action)) {
            $this->generateDossier();

            return;
        }

        if ($intents->wantsHuman($action)) {
            $this->respondWithHumanSupport();

            return;
        }

        $this->submitUserText($action, 'followup');
    }

    /**
     * "Ja, genau" on a clarification (CI phase 1): answer the confirmed concerns in full.
     */
    public function confirmUnderstanding(string $messageId): void
    {
        $index = $this->awaitingClarificationIndex($messageId);
        if ($index === null) {
            return;
        }

        $this->addMessage([
            'role' => 'user',
            'content' => $this->t('answer.confirm_yes'),
        ]);

        $this->queryAiModel($this->concernTextFor($index), 'answer');
    }

    /**
     * "Nicht ganz" on a clarification: ask one targeted follow-up instead of starting over.
     */
    public function rejectUnderstanding(string $messageId): void
    {
        $index = $this->awaitingClarificationIndex($messageId);
        if ($index === null) {
            return;
        }

        $this->addMessage([
            'role' => 'user',
            'content' => $this->t('answer.confirm_no'),
        ]);

        $this->queryAiModel($this->concernTextFor($index), 'correct');
    }

    /**
     * Index of the given message if it is a clarification that still waits for an answer (the latest message).
     */
    protected function awaitingClarificationIndex(string $messageId): ?int
    {
        $index = array_key_last($this->messages);
        $message = $index === null ? null : $this->messages[$index];

        if ($message === null || $message['id'] !== $messageId || $message['role'] !== 'assistant') {
            return null;
        }

        return MessageParser::parseWithNextSteps((string) $message['content'], $this->locale)['kind'] === 'clarify' ? $index : null;
    }

    /**
     * The citizen's own words plus the confirmed concerns, used for retrieval instead of "Ja, genau".
     */
    protected function concernTextFor(int $clarificationIndex): string
    {
        $parts = [];

        for ($i = $clarificationIndex - 1; $i >= 0; $i--) {
            if ($this->messages[$i]['role'] === 'user') {
                $parts[] = $this->messages[$i]['content'];
                break;
            }
        }

        $parsed = MessageParser::parseWithNextSteps((string) $this->messages[$clarificationIndex]['content'], $this->locale);

        return trim(implode("\n", [...$parts, ...($parsed['clarify']['items'] ?? [])]));
    }

    public function startWorkflow(string $name): void
    {
        $this->activeWorkflow = $name;

        if ($name === 'passport') {
            $this->addMessage([
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $this->t('assistant.passport_intro'),
                'timestamp' => now()->format('H:i'),
                'workflow' => 'passport',
                'sources' => app(KnowledgeBaseService::class)->getOfficialSources('passport', $this->locale),
            ]);
        } elseif ($name === 'residence') {
            $this->addMessage([
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $this->t('assistant.residence_intro'),
                'timestamp' => now()->format('H:i'),
                'workflow' => 'residence',
                'sources' => app(KnowledgeBaseService::class)->getOfficialSources('residence', $this->locale),
            ]);
        }
    }

    public function resetWorkflow(): void
    {
        $this->activeWorkflow = null;
    }

    // --- Passport Workflow Methods ---

    public function setPassportScenario(string $scenario): void
    {
        $wf = new PassportWorkflow($this->passportState);
        $wf->setScenario($scenario);
        $this->passportState = $wf->state;

        $scenarioLabels = Translate::map('assistant.passport_scenario', $this->locale);
        $label = is_string($scenarioLabels[$scenario] ?? null) ? $scenarioLabels[$scenario] : $scenario;

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $label,
            'timestamp' => now()->format('H:i'),
        ]);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $this->t('assistant.passport_scenario_ack', ['label' => $label]),
            'timestamp' => now()->format('H:i'),
            'workflow' => 'passport',
            'sources' => app(KnowledgeBaseService::class)->getOfficialSources('passport', $this->locale),
        ]);
    }

    public function setPassportUrgency(string $urgency): void
    {
        $wf = new PassportWorkflow($this->passportState);
        $wf->setUrgency($urgency);
        $this->passportState = $wf->state;

        $fee = $wf->getFeeInfo($this->locale);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $this->t('assistant.passport_overview', ['fee' => $fee['formatted'], 'duration' => $fee['duration'], 'validity' => $fee['validity']]),
            'timestamp' => now()->format('H:i'),
            'workflow' => 'passport',
            'sources' => app(KnowledgeBaseService::class)->getOfficialSources('passport', $this->locale),
        ]);
    }

    public function togglePassportItem(string $key): void
    {
        $wf = new PassportWorkflow($this->passportState);
        $wf->toggleItem($key);
        $this->passportState = $wf->state;
    }

    // --- Residence Workflow Methods ---

    public function setResidenceMoveStatus(string $status, int $days = 0): void
    {
        $wf = new ResidenceWorkflow($this->residenceState);
        $wf->setMoveStatus($status, $days);
        $this->residenceState = $wf->state;

        $deadline = $wf->getDeadlineStatus($this->locale);

        $statusText = $status === 'planning_to_move'
            ? $this->t('assistant.residence_planning')
            : $this->t('assistant.residence_moved', ['days' => $days]);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $statusText,
            'timestamp' => now()->format('H:i'),
        ]);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => "ℹ️ **{$deadline['warning']}**\n\n".$this->t('assistant.residence_type_question'),
            'timestamp' => now()->format('H:i'),
            'workflow' => 'residence',
            'sources' => app(KnowledgeBaseService::class)->getOfficialSources('residence', $this->locale),
        ]);
    }

    public function setResidenceType(string $type): void
    {
        $wf = new ResidenceWorkflow($this->residenceState);
        $wf->setResidenceType($type);
        $this->residenceState = $wf->state;

        $label = $this->t($type === 'hauptwohnsitz' ? 'assistant.residence_type_primary' : 'assistant.residence_type_secondary');

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $label,
            'timestamp' => now()->format('H:i'),
        ]);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $this->t('assistant.residence_housing_question'),
            'timestamp' => now()->format('H:i'),
            'workflow' => 'residence',
            'sources' => app(KnowledgeBaseService::class)->getOfficialSources('residence', $this->locale),
        ]);
    }

    public function setResidenceHousing(string $housing): void
    {
        $wf = new ResidenceWorkflow($this->residenceState);
        $wf->setHousingType($housing);
        $this->residenceState = $wf->state;

        $label = $this->t($housing === 'rented' ? 'assistant.residence_housing_rented' : 'assistant.residence_housing_owner');

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $label,
            'timestamp' => now()->format('H:i'),
        ]);

        $hint = $this->t($housing === 'rented' ? 'assistant.residence_hint_rented' : 'assistant.residence_hint_owner');

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => "{$hint}\n\n".$this->t('assistant.residence_channel_question'),
            'timestamp' => now()->format('H:i'),
            'workflow' => 'residence',
            'sources' => app(KnowledgeBaseService::class)->getOfficialSources('residence', $this->locale),
        ]);
    }

    public function setResidenceChannel(string $channel): void
    {
        $wf = new ResidenceWorkflow($this->residenceState);
        $wf->setChannel($channel);
        $this->residenceState = $wf->state;

        $label = $this->t($channel === 'id_austria' ? 'assistant.residence_channel_id_austria' : 'assistant.residence_channel_in_person');

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $label,
            'timestamp' => now()->format('H:i'),
        ]);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $this->t('assistant.residence_checklist_intro'),
            'timestamp' => now()->format('H:i'),
            'workflow' => 'residence',
            'sources' => app(KnowledgeBaseService::class)->getOfficialSources('residence', $this->locale),
        ]);
    }

    public function toggleResidenceItem(string $key): void
    {
        $wf = new ResidenceWorkflow($this->residenceState);
        $wf->toggleItem($key);
        $this->residenceState = $wf->state;
    }

    // --- Message Processing & OpenRouter AI Integration ---

    public function sendMessage(): void
    {
        $this->submitUserText($this->inputMessage);
    }

    /**
     * @param  string  $phase  answer-pattern phase passed to the model, see KnowledgeBaseService::buildPhaseInstruction()
     */
    protected function submitUserText(string $text, string $phase = 'auto'): void
    {
        $rawText = trim($text);
        if ($rawText === '') {
            return;
        }

        $this->piiWarning = null;

        // Privacy check: detect Austrian SVNr pattern (4 digits + 6 digits date of birth)
        if (preg_match('/\b(\d{4})\s?(\d{6})\b/', $rawText)) {
            $this->piiWarning = $this->t('chat.pii_warning');
            $rawText = (string) preg_replace('/\b(\d{4})\s?(\d{6})\b/', $this->t('chat.svnr_redacted'), $rawText);
        }

        // Add user message to conversation
        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $rawText,
            'timestamp' => now()->format('H:i'),
        ]);

        $this->inputMessage = '';
        $this->isLoading = true;

        // Intent detection (all languages): check if citizen explicitly requests a human
        $intents = app(IntentMatcher::class);
        if ($intents->wantsHuman($rawText)) {
            // Escalation reasons are for the (German-speaking) staff portal.
            $this->respondWithHumanSupport($this->t('assistant.escalation_reason_chat', [], Language::German));
            $this->isLoading = false;

            return;
        }

        // Detect guided workflow triggers
        if ($this->activeWorkflow === null) {
            $this->activeWorkflow = $intents->workflowFor($rawText);
        }

        $this->queryAiModel($rawText, $phase);
    }

    protected function queryAiModel(string $userPrompt, string $phase = 'auto'): void
    {
        $kbService = app(KnowledgeBaseService::class);
        $aiClient = app(AiAssistantInterface::class);

        // Build conversation messages for API
        $systemPrompt = $kbService->buildSystemPrompt($this->locale);

        // 1. Retrieve matching factual knowledge nuggets from database
        $nuggets = KnowledgeNugget::findRelevantNuggets($userPrompt, 2);

        // 2. Numbered sources the answer may cite with [n] (citation module, swappable for a verification backend)
        $citations = app(CitationResolver::class);
        $topics = array_values(array_unique(array_filter([$this->activeWorkflow, ...$kbService->detectDomains($userPrompt)])));
        $sources = $citations->resolve($userPrompt, $topics, $this->locale, $nuggets);

        if ($nuggets->isNotEmpty()) {
            $factsList = [];
            foreach ($nuggets as $nugget) {
                $number = collect($sources)->first(fn (CitedSource $s) => rtrim($s->url, '/') === rtrim((string) $nugget->official_source_url, '/'))?->number;
                $sourceHint = $number ? " (source [{$number}])" : '';
                $factsList[] = "### [OFFICIAL FACTS: {$nugget->title}]{$sourceHint}\n{$nugget->factual_context}";
            }

            // One language-neutral instruction; the answer language comes from the system prompt.
            $systemPrompt .= "\n\nRELEVANT STATUTORY FACTS RETRIEVED FROM DATABASE:\n".implode("\n\n", $factsList)
                ."\n\nIMPORTANT PERSONALIZATION INSTRUCTION:\nUse these verified facts as your legal baseline. You MUST tailor your response specifically to this citizen's unique situation, age, nationality, and details already established in the conversation history above (avoid generic cookie-cutter answers)!";
        }

        $systemPrompt = trim(implode("\n\n", array_filter([
            $systemPrompt,
            $kbService->buildSourcesSection($sources, $this->locale),
            $kbService->buildPhaseInstruction($phase, $this->locale),
        ])));

        $apiMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Include recent history for conversational continuity. Source numbers are per answer, so earlier markers are removed.
        $recent = array_slice($this->messages, -8);
        foreach ($recent as $msg) {
            if (in_array($msg['role'], ['user', 'assistant'])) {
                $apiMessages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['role'] === 'assistant' ? CitationMarkers::strip($msg['content']) : $msg['content'],
                ];
            }
        }

        $response = $aiClient->chat($apiMessages, [
            'temperature' => 0.3,
            'max_tokens' => 3500,
        ]);

        $this->isLoading = false;

        $hasValidContent = $response['success'] && trim($response['content']) !== '';

        if ($hasValidContent) {
            $content = trim($response['content']);
            $isClarification = (bool) preg_match(MessageParser::CLARIFY_MARKER, trim(strtok($content, "\n") ?: ''));

            // After "Ja, genau" the answer must not ask again.
            if ($isClarification && $phase === 'answer') {
                $content = trim((string) preg_replace('/^.*\n?/u', '', $content, 1));
                $isClarification = false;
            }

            $sources = $isClarification ? [] : $citations->finalize($sources, $content);

            $this->addMessage([
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $content,
                'timestamp' => now()->format('H:i'),
                'sources' => array_map(fn (CitedSource $s) => $s->toArray(), $sources),
                'workflow' => $this->activeWorkflow,
                'is_fallback' => false,
            ]);

            // Asynchronously / safely attempt knowledge distillation for novel interactions
            if (! $isClarification) {
                try {
                    app(KnowledgeDistiller::class)->learnFromInteraction($userPrompt, CitationMarkers::strip($content), $this->locale);
                } catch (\Throwable) {
                }
            }
        } else {
            // Graceful Knowledge Base Fallback
            $fallback = $kbService->getFallbackResponse($userPrompt, $this->locale);

            $notice = $this->t('assistant.limited_mode_notice')."\n\n";

            $this->addMessage([
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $this->withContentFallbackNotice($notice.$fallback['content']),
                'timestamp' => now()->format('H:i'),
                'sources' => $fallback['sources'],
                'checklist' => $fallback['checklist'],
                'workflow' => $this->activeWorkflow ?? $fallback['topic'],
                'is_fallback' => true,
            ]);
        }
    }

    public function respondWithHumanSupport(?string $reason = null): void
    {
        $kb = app(KnowledgeBaseService::class);
        $sources = $kb->getOfficialSources('human', $this->locale);

        $content = $this->withContentFallbackNotice($this->t('assistant.human_support'));

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $content,
            'timestamp' => now()->format('H:i'),
            'sources' => $sources,
            'is_human_request' => true,
        ]);

        $this->markConversationAsEscalated($reason ?? $this->t('assistant.escalation_reason_default', [], Language::German));
    }

    public function generateDossier(): void
    {
        $this->dossierContent = $this->buildEscalationDossier();

        if ($this->conversationId) {
            try {
                Conversation::where('id', $this->conversationId)->update([
                    'case_dossier' => $this->buildEscalationDossier(Language::German->value),
                ]);
            } catch (\Throwable) {
            }
        }

        $this->showDossierModal = true;
    }

    public function closeDossierModal(): void
    {
        $this->showDossierModal = false;
    }

    public function clearConversation(): void
    {
        $this->startNewConversation();
    }

    public function getModelDisplayName(): string
    {
        $model = (string) config('openrouter.model', 'poolside/laguna-s-2.1:free');
        if (str_contains($model, 'laguna')) {
            return 'Laguna-S 2.1 (Free)';
        }
        if (str_contains($model, 'deepseek')) {
            return 'DeepSeek V4 Flash';
        }

        return $model;
    }

    public function checkForUpdates(): void
    {
        if (! $this->conversationId || $this->isLoading) {
            return;
        }

        try {
            $conv = Conversation::with(['messages'])->find($this->conversationId);
            if (! $conv) {
                return;
            }

            if (! empty($conv->assigned_officer)) {
                $this->isOfficerJoined = true;
                $this->officerJoinedName = $conv->assigned_officer;
            }

            $existingIds = array_column($this->messages, 'id');
            $hasNew = false;

            foreach ($conv->messages as $m) {
                $id = $m->uuid ?? (string) $m->id;
                if (! in_array($id, $existingIds, true)) {
                    $hasNew = true;
                    $content = (string) $m->content;

                    $this->messages[] = $this->messageFromModel($m);

                    if (OfficerMessages::kind(['workflow' => $m->workflow, 'content' => $content]) === 'joined') {
                        $this->isOfficerJoined = true;
                        if (! empty($conv->assigned_officer)) {
                            $this->officerJoinedName = $conv->assigned_officer;
                        }
                    }
                }
            }

            if ($hasNew) {
                $this->updateEscalatedCount();
            }
        } catch (\Throwable) {
            // Memory or testing fallback
        }
    }

    /**
     * @param  array<string, string|int|float>  $replace
     */
    protected function t(string $key, array $replace = [], Language|string|null $locale = null): string
    {
        $locale = $locale instanceof Language ? $locale->value : ($locale ?? $this->locale);

        return Translate::text($key, $replace, $locale);
    }

    /**
     * Languages without their own reviewed content get the stored German/English text plus a short notice.
     */
    protected function withContentFallbackNotice(string $content): string
    {
        $language = $this->language();
        $servedIn = collect($language->contentChain())->first(fn (Language $l) => Translate::hasOwn('assistant.welcome', $l->value)) ?? Language::German;

        if ($servedIn === $language || $language->isSimple()) {
            return $content;
        }

        return '*'.$this->t('chat.content_fallback_notice', ['language' => $servedIn->nativeName()])."*\n\n---\n\n".$content;
    }

    public function render(): View
    {
        $passportWf = new PassportWorkflow($this->passportState);
        $residenceWf = new ResidenceWorkflow($this->residenceState);

        return view('livewire.austrian-gov-assistant', [
            'language' => $this->language(),
            'passportChecklist' => $passportWf->getChecklist($this->locale),
            'passportFee' => $passportWf->getFeeInfo($this->locale),
            'residenceChecklist' => $residenceWf->getChecklist($this->locale),
            'residenceDeadline' => $residenceWf->getDeadlineStatus($this->locale),
        ]);
    }
}
