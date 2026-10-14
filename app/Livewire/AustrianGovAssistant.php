<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\KnowledgeNugget;
use App\Services\Ai\Contracts\AiAssistantInterface;
use App\Services\AustrianGov\KnowledgeBaseService;
use App\Services\AustrianGov\KnowledgeDistiller;
use App\Services\AustrianGov\Workflows\PassportWorkflow;
use App\Services\AustrianGov\Workflows\ResidenceWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Digitales Österreich 2030 - KI-Bürgerassistenz')]
class AustrianGovAssistant extends Component
{
    public ?int $conversationId = null;

    public int $escalatedCasesCount = 0;

    public string $locale = 'de';

    /**
     * @var array<int, array{
     *     id: string,
     *     role: string,
     *     content: string,
     *     timestamp: string,
     *     sources?: array<int, array{title: string, url: string, description: string, tag: string}>,
     *     workflow?: ?string,
     *     is_fallback?: bool,
     *     checklist?: array<int, string>,
     *     is_human_request?: bool
     * }>
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
    public array $passportState = [
        'step' => 1,
        'scenario' => null,
        'urgency' => 'standard',
        'age_group' => 'adult',
        'checked_items' => [],
        'authority_location' => null,
    ];

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
    public array $residenceState = [
        'step' => 1,
        'move_status' => null,
        'days_since_move' => 0,
        'residence_type' => 'hauptwohnsitz',
        'housing_type' => 'rented',
        'channel' => 'id_austria',
        'checked_items' => [],
    ];

    public bool $showDossierModal = false;

    public string $dossierContent = '';

    public string $textSize = 'normal'; // 'normal', 'large', 'xlarge'

    public bool $highContrast = false;

    public bool $simpleLanguage = false;

    public function mount(KnowledgeBaseService $kbService): void
    {
        $this->locale = session('locale', 'de');
        $this->loadOrCreateConversation();
    }

    public function loadOrCreateConversation(): void
    {
        try {
            $sessionConvId = session('austrian_gov_conversation_id');
            $conversation = null;

            if ($sessionConvId) {
                $conversation = Conversation::with('messages')->find($sessionConvId);
            }

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
                $this->locale = $conversation->locale ?? $this->locale;
                $this->activeWorkflow = $conversation->active_workflow;

                if (! empty($conversation->workflow_data)) {
                    if (isset($conversation->workflow_data['passportState'])) {
                        $this->passportState = array_merge($this->passportState, $conversation->workflow_data['passportState']);
                    }
                    if (isset($conversation->workflow_data['residenceState'])) {
                        $this->residenceState = array_merge($this->residenceState, $conversation->workflow_data['residenceState']);
                    }
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
                        if ($m->workflow === 'officer_joined' || str_contains($content, 'ist dem Gespräch beigetreten') || str_contains($content, 'has joined the conversation')) {
                            $this->isOfficerJoined = true;
                            if (! empty($conversation->assigned_officer)) {
                                $this->officerJoinedName = $conversation->assigned_officer;
                            }
                        }

                        if ($m->role === 'user') {
                            $lastUserText = $content;
                            $repairedMessages[] = [
                                'id' => $m->uuid ?? (string) Str::uuid(),
                                'role' => $m->role,
                                'content' => $content,
                                'timestamp' => $m->created_at?->format('H:i') ?? now()->format('H:i'),
                                'sources' => $m->sources ?? [],
                                'workflow' => $m->workflow,
                                'is_fallback' => (bool) $m->is_fallback,
                            ];
                        } elseif ($m->role === 'assistant') {
                            if (trim($content) === '') {
                                $fallback = app(KnowledgeBaseService::class)->getFallbackResponse($lastUserText ?? 'general', $this->locale);
                                $content = $fallback['content'];
                                $m->update(['content' => $content, 'is_fallback' => true]);
                            }

                            $repairedMessages[] = [
                                'id' => $m->uuid ?? (string) Str::uuid(),
                                'role' => $m->role,
                                'content' => $content,
                                'timestamp' => $m->created_at?->format('H:i') ?? now()->format('H:i'),
                                'sources' => $m->sources ?? [],
                                'workflow' => $m->workflow,
                                'is_fallback' => (bool) $m->is_fallback,
                            ];
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
        $this->passportState = [
            'step' => 1,
            'scenario' => null,
            'urgency' => 'standard',
            'age_group' => 'adult',
            'checked_items' => [],
            'authority_location' => null,
        ];
        $this->residenceState = [
            'step' => 1,
            'move_status' => null,
            'days_since_move' => 0,
            'residence_type' => 'hauptwohnsitz',
            'housing_type' => 'rented',
            'channel' => 'id_austria',
            'checked_items' => [],
        ];
        $this->inputMessage = '';
        $this->messages = [];
        $this->showDossierModal = false;
        $this->dossierContent = '';
        $this->escalationSuccess = false;

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

    public function persistMessage(array $msgData): void
    {
        if (! $this->conversationId) {
            return;
        }

        try {
            ConversationMessage::create([
                'conversation_id' => $this->conversationId,
                'uuid' => $msgData['id'] ?? (string) Str::uuid(),
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

    protected function addMessage(array $msg): array
    {
        if (empty($msg['id'])) {
            $msg['id'] = (string) Str::uuid();
        }
        if (empty($msg['timestamp'])) {
            $msg['timestamp'] = now()->format('H:i');
        }

        $this->messages[] = $msg;
        $this->persistMessage($msg);

        return $msg;
    }

    public function markConversationAsEscalated(string $reason): void
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

    public function buildEscalationDossier(): string
    {
        if ($this->activeWorkflow === 'passport') {
            return (new PassportWorkflow($this->passportState))->generateDossierSummary($this->locale);
        }

        if ($this->activeWorkflow === 'residence') {
            return (new ResidenceWorkflow($this->residenceState))->generateDossierSummary($this->locale);
        }

        $userMsgs = collect($this->messages)
            ->where('role', 'user')
            ->pluck('content')
            ->implode("\n* ");

        return $this->locale === 'de'
            ? "### Bürger-Dossier (Menschliche Sachbearbeitung angefordert)\n\n"
              .'* **Datum/Uhrzeit:** '.now()->format('d.m.Y H:i')." Uhr\n"
              ."* **Status:** Offen für Sachbearbeiter-Prüfung\n"
              ."* **Sprache:** Deutsch\n\n"
              ."#### Bürger-Anfragen im Chat:\n* ".($userMsgs ?: 'Allgemeine behördliche Auskunftsanfrage')
            : "### Citizen Dossier (Escalated to Civil Servant)\n\n"
              .'* **Date/Time:** '.now()->format('d.m.Y H:i')."\n"
              ."* **Status:** Pending civil servant review\n"
              ."* **Language:** English\n\n"
              ."#### Citizen inquiries in chat:\n* ".($userMsgs ?: 'General public service inquiry');
    }

    public function updateEscalatedCount(): void
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
        if (in_array($locale, ['de', 'en'])) {
            $this->locale = $locale;
            session(['locale' => $locale]);

            if ($this->conversationId) {
                try {
                    Conversation::where('id', $this->conversationId)->update(['locale' => $locale]);
                } catch (\Throwable) {
                }
            }

            // Add notification message in conversation
            $isGerman = $locale === 'de';
            $this->addMessage([
                'id' => (string) Str::uuid(),
                'role' => 'system',
                'content' => $isGerman
                    ? 'Sprache auf Deutsch umgestellt. Wie kann ich Ihnen bei Ihren Behördenwegen behilflich sein?'
                    : 'Language switched to English. How can I assist you with Austrian public services today?',
                'timestamp' => now()->format('H:i'),
            ]);
        }
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

    public function toggleSimpleLanguage(): void
    {
        $this->simpleLanguage = ! $this->simpleLanguage;
    }

    public function initWelcomeMessage(): void
    {
        $isGerman = $this->locale === 'de';

        $this->messages = [
            [
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $isGerman
                    ? "Grüß Gott! Ich bin Ihre offizielle **KI-Bürgerassistenz der Republik Österreich**.\n\nIch übersetze Amtsdeutsch in einfache, klare Schritte und helfe Ihnen bei amtlichen Erledigungen – ohne langes Suchen. Wählen Sie ein Anliegen oder fragen Sie einfach in Ihren eigenen Worten:"
                    : "Welcome! I am your official **AI Citizen Assistant for the Republic of Austria**.\n\nI explain Austrian administrative procedures in plain language and guide you through public services step by step. Feel free to choose an option below or ask your question directly:",
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

        $lower = mb_strtolower($action);
        if (str_contains($lower, 'dossier') || str_contains($lower, 'zusammenfassung herunterladen') || str_contains($lower, 'download dossier')) {
            $this->generateDossier();

            return;
        }

        if (str_contains($lower, 'mensch') || str_contains($lower, 'human') || str_contains($lower, 'sachbearbeiter') || str_contains($lower, 'hotline')) {
            $this->respondWithHumanSupport();

            return;
        }

        $this->inputMessage = $action;
        $this->sendMessage();
    }

    public function startWorkflow(string $name): void
    {
        $this->activeWorkflow = $name;
        $isGerman = $this->locale === 'de';

        if ($name === 'passport') {
            $this->addMessage([
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $isGerman
                    ? "### Interaktiver Reisepass-Assistent\n\nIch begleite Sie Schritt für Schritt durch die Ausstellung Ihres neuen Reisepasses. Bitte wählen Sie Ihre aktuelle Ausgangssituation:"
                    : "### Interactive Passport Assistant\n\nI will guide you step by step through your Austrian passport application. Please select your current situation:",
                'timestamp' => now()->format('H:i'),
                'workflow' => 'passport',
                'sources' => app(KnowledgeBaseService::class)->getOfficialSources('passport', $this->locale),
            ]);
        } elseif ($name === 'residence') {
            $this->addMessage([
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $isGerman
                    ? "### Interaktiver Wohnsitz-Assistent (Meldewesen)\n\nIn Österreich gilt eine gesetzliche Meldepflicht von 3 Tagen ab Einzug. Wann sind Sie umgezogen bzw. planen Sie den Umzug?"
                    : "### Interactive Residence Registration Assistant\n\nUnder Austrian law, you must register your address within 3 days of moving in. Have you already moved or are you planning to move?",
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

        $isGerman = $this->locale === 'de';
        $scenarioLabels = [
            'expiring' => $isGerman ? 'Reisepass läuft bald ab' : 'Passport expiring soon',
            'expired_recently' => $isGerman ? 'Reisepass kürzlich abgelaufen (< 5 Jahre)' : 'Passport expired recently (< 5 years)',
            'expired_over_5_years' => $isGerman ? 'Reisepass seit über 5 Jahren abgelaufen' : 'Passport expired > 5 years ago',
            'lost_stolen' => $isGerman ? 'Reisepass verloren oder gestohlen' : 'Passport lost or stolen',
            'child' => $isGerman ? 'Reisepass für ein Kind' : 'Passport for a child',
        ];

        $label = $scenarioLabels[$scenario] ?? $scenario;

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $label,
            'timestamp' => now()->format('H:i'),
        ]);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $isGerman
                ? "Verstanden: **{$label}**.\n\nWie schnell benötigen Sie das Dokument? Wählen Sie bitte die gewünschte Zustell-Geschwindigkeit:"
                : "Noted: **{$label}**.\n\nHow quickly do you need your passport? Please choose your delivery speed:",
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

        $fee = $wf->getFeeInfo();
        $isGerman = $this->locale === 'de';

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $isGerman
                ? "### Ihre persönliche Reisepass-Übersicht\n\n* **Offizielle Gebühr:** **{$fee['formatted']}**\n* **Lieferzeit:** {$fee['duration']}\n* **Gültigkeit:** {$fee['validity']}\n\nHier ist Ihre persönliche Dokumenten-Checkliste. Haken Sie die Punkte ab, sobald Sie diese vorbereitet haben:"
                : "### Your Personalized Passport Overview\n\n* **Official Fee:** **{$fee['formatted']}**\n* **Processing Time:** {$fee['duration']}\n* **Validity:** {$fee['validity']}\n\nHere is your required document checklist. Check off items as you prepare them:",
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
        $isGerman = $this->locale === 'de';

        $statusText = $status === 'planning_to_move'
            ? ($isGerman ? 'Ich plane einen Umzug' : 'I am planning to move')
            : ($isGerman ? "Ich bin bereits umgezogen ({$days} Tage)" : "I have already moved ({$days} days ago)");

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $statusText,
            'timestamp' => now()->format('H:i'),
        ]);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => "ℹ️ **{$deadline['warning']}**\n\n".($isGerman
                ? 'Handelt es sich bei der neuen Unterkunft um Ihren **Hauptwohnsitz** (Mittelpunkt der Lebensbeziehungen) oder einen **Nebenwohnsitz**?'
                : 'Is this new address your **Primary Residence** (*Hauptwohnsitz* - center of vital interests) or a **Secondary Residence** (*Nebenwohnsitz*)?'),
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

        $isGerman = $this->locale === 'de';
        $label = $type === 'hauptwohnsitz'
            ? ($isGerman ? 'Hauptwohnsitz' : 'Primary Residence')
            : ($isGerman ? 'Nebenwohnsitz' : 'Secondary Residence');

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $label,
            'timestamp' => now()->format('H:i'),
        ]);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $isGerman
                ? 'Wohnen Sie in einer **Mietwohnung** oder sind Sie **Eigentümerin / Eigentümer** der Immobilie?'
                : 'Are you living in a **rented flat** or are you the **property owner**?',
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

        $isGerman = $this->locale === 'de';
        $label = $housing === 'rented'
            ? ($isGerman ? 'Mietwohnung (Miete)' : 'Rented property')
            : ($isGerman ? 'Eigentum (Haus/Wohnung)' : 'Property owner');

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $label,
            'timestamp' => now()->format('H:i'),
        ]);

        $hint = $housing === 'rented'
            ? ($isGerman
                ? '⚠️ **Wichtig:** Da Sie zur Miete wohnen, muss die Unterkunftgeberin / der Vermieter den Meldezettel im Original oder digital bestätigen.'
                : '⚠️ **Key Rule:** As a tenant, your landlord/property manager must sign the *Meldezettel* form.')
            : ($isGerman
                ? 'Als Eigentümerin/Eigentümer unterschreiben Sie den Meldezettel selbst im Abschnitt Unterkunftgeber.'
                : 'As property owner, you sign the landlord section yourself.');

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => "{$hint}\n\n".($isGerman
                ? 'Wie möchten Sie die Meldung durchführen? Wählen Sie den gewünschten Kanal:'
                : 'Which registration method do you prefer?'),
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

        $isGerman = $this->locale === 'de';
        $label = $channel === 'id_austria'
            ? ($isGerman ? 'Digital über ID Austria (App Digitales Amt)' : 'Digital via ID Austria')
            : ($isGerman ? 'Persönlich am Meldeamt / Gemeindeamt' : 'In person at municipal office');

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'user',
            'content' => $label,
            'timestamp' => now()->format('H:i'),
        ]);

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $isGerman
                ? "### Checkliste für Ihre Wohnsitzanmeldung\n\n* **Gebühr:** **0,00 €** (Vollständig gebührenfrei!)\n* **Frist:** Binnen 3 Werktagen ab Einzug\n* **Abmeldung alter Wohnsitz:** Erfolgt automatisch bei Neuanmeldung!\n\nHier ist Ihre Checkliste inklusive wichtiger Folge-Erledigungen (KFZ, ORF-Beitrag):"
                : "### Checklist for your Residence Registration\n\n* **Cost:** **€0.00** (Completely free by law)\n* **Deadline:** Within 3 working days of moving in\n* **Previous address:** Automatically deregistered!\n\nHere is your interactive checklist including mandatory follow-ups:",
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
        $rawText = trim($this->inputMessage);
        if ($rawText === '') {
            return;
        }

        $this->piiWarning = null;

        // Privacy check: detect Austrian SVNr pattern (4 digits + 6 digits date of birth)
        if (preg_match('/\b(\d{4})\s?(\d{6})\b/', $rawText)) {
            $this->piiWarning = $this->locale === 'de'
                ? 'Datenschutz-Hinweis: Ihre Sozialversicherungsnummer wurde aus Sicherheitsgründen automatisch unkenntlich gemacht. Bitte übermitteln Sie keine vertraulichen Identifikationsnummern.'
                : 'Privacy Notice: Your Austrian Social Security Number was automatically redacted for privacy. Please do not submit confidential identification numbers.';
            $rawText = (string) preg_replace('/\b(\d{4})\s?(\d{6})\b/', '[SVNr geschwärzt]', $rawText);
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

        // Intent detection: check if citizen explicitly requests a human
        $isGerman = $this->locale === 'de';
        $lower = mb_strtolower($rawText);
        if (str_contains($lower, 'mensch') || str_contains($lower, 'berater') || str_contains($lower, 'beamte') || str_contains($lower, 'human') || str_contains($lower, 'telefon')) {
            $this->respondWithHumanSupport($isGerman ? 'Bürgeranfrage im Chat nach persönlicher Beratung' : 'Citizen requested human caseworker assistance in chat');
            $this->isLoading = false;

            return;
        }

        // Detect guided workflow triggers
        if ($this->activeWorkflow === null) {
            if (str_contains($lower, 'pass') || str_contains($lower, 'reisepass') || str_contains($lower, 'passport')) {
                $this->activeWorkflow = 'passport';
            } elseif (str_contains($lower, 'umzug') || str_contains($lower, 'umgemeldet') || str_contains($lower, 'wohnsitz') || str_contains($lower, 'meldezettel') || str_contains($lower, 'moving')) {
                $this->activeWorkflow = 'residence';
            }
        }

        $this->queryAiModel($rawText);
    }

    protected function queryAiModel(string $userPrompt): void
    {
        $kbService = app(KnowledgeBaseService::class);
        $aiClient = app(AiAssistantInterface::class);

        // Build conversation messages for API
        $systemPrompt = $kbService->buildSystemPrompt($this->locale);
        if ($this->simpleLanguage) {
            $systemPrompt .= "\n\nZUSATZ-ANWEISUNG: Formuliere in 'Leichter Sprache' (A2-Niveau), kurze Hauptsätze, keine Fremdwörter, maximale Barrierefreiheit!";
        }

        // 1. Retrieve matching factual knowledge nuggets from database
        $nuggets = KnowledgeNugget::findRelevantNuggets($userPrompt, 2);
        if ($nuggets->isNotEmpty()) {
            $factsList = [];
            foreach ($nuggets as $nugget) {
                $factsList[] = "### [BEHÖRDLICHE FAKTEN: {$nugget->title}]\n{$nugget->factual_context}";
            }
            $dbGrounding = implode("\n\n", $factsList);

            if ($this->locale === 'de') {
                $systemPrompt .= "\n\nRELEVANTE RECHTLICHE FAKTEN AUS DER BUNDES-DATENBANK:\n{$dbGrounding}\n\nWICHTIGE ANWEISUNG ZUR PERSONALISIERUNG:\nNutze diese Fakten als rechtliche Richtschnur. Gehe dabei ZWINGEND spezifisch auf die persönliche Situation, das Alter, die Staatsbürgerschaft und bereits genannte Details des Bürgers aus dem bisherigen Gesprächsverlauf ein (keine unpersönlichen Pauschalantworten)!";
            } else {
                $systemPrompt .= "\n\nRELEVANT STATUTORY FACTS RETRIEVED FROM DATABASE:\n{$dbGrounding}\n\nIMPORTANT PERSONALIZATION INSTRUCTION:\nUse these verified facts as your legal baseline. You MUST tailor your response specifically to this citizen's unique situation, age, nationality, and details already established in the conversation history above (avoid generic cookie-cutter answers)!";
            }
        }

        $apiMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Include recent history for conversational continuity
        $recent = array_slice($this->messages, -8);
        foreach ($recent as $msg) {
            if (in_array($msg['role'], ['user', 'assistant'])) {
                $apiMessages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ];
            }
        }

        $response = $aiClient->chat($apiMessages, [
            'temperature' => 0.3,
            'max_tokens' => 3500,
        ]);

        $this->isLoading = false;

        $hasValidContent = $response['success'] && ! empty(trim((string) ($response['content'] ?? '')));

        if ($hasValidContent) {
            $topic = $this->activeWorkflow ?? $kbService->detectDomain($userPrompt);
            $sources = $kbService->getOfficialSources($topic, $this->locale);

            $this->addMessage([
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $response['content'],
                'timestamp' => now()->format('H:i'),
                'sources' => $sources,
                'workflow' => $this->activeWorkflow,
                'is_fallback' => false,
            ]);

            // Asynchronously / safely attempt knowledge distillation for novel interactions
            try {
                app(KnowledgeDistiller::class)->learnFromInteraction($userPrompt, $response['content'], $this->locale);
            } catch (\Throwable) {
            }
        } else {
            // Graceful Knowledge Base Fallback
            $fallback = $kbService->getFallbackResponse($userPrompt, $this->locale);

            $notice = $this->locale === 'de'
                ? "> [!NOTE]\n> **Verifizierter Wissensdatenbank-Modus:** Der Live-KI-Dienst steht gerade unter hoher Last oder ist vorübergehend nicht erreichbar. Sie erhalten geprüfte offizielle Verwaltungsangaben aus der österreichischen Rechtsdatenbank.\n\n"
                : "> [!NOTE]\n> **Verified Knowledge-Base Mode:** The live AI service is currently unavailable or under heavy load. Below is verified official administrative guidance from Austrian government resources.\n\n";

            $this->addMessage([
                'id' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => $notice.$fallback['content'],
                'timestamp' => now()->format('H:i'),
                'sources' => $fallback['sources'],
                'checklist' => $fallback['checklist'] ?? null,
                'workflow' => $fallback['topic'] ?? $this->activeWorkflow,
                'is_fallback' => true,
            ]);
        }
    }

    public function respondWithHumanSupport(?string $reason = null): void
    {
        $isGerman = $this->locale === 'de';
        $kb = app(KnowledgeBaseService::class);
        $sources = $kb->getOfficialSources('human', $this->locale);

        $content = $isGerman
            ? "### Persönliche menschliche Unterstützung (Fall an Beamten-Portal übermittelt)\n\nGerne vermittle ich Sie an die offiziellen menschlichen Ansprechpartner der österreichischen Verwaltung. Ihr Fall wurde erfasst und ist nun im **Beamtinnen- & Beamten-Portal** zur Bearbeitung vorgemerkt.\n\n* **Bürgerservice des Bundeskanzleramts:**\n  * 📞 **Telefon:** [+43 1 53115-0](tel:+431531150) (Montag bis Freitag, 08:00 – 16:00 Uhr)\n  * ✉️ **E-Mail:** [buergerservice@bka.gv.at](mailto:buergerservice@bka.gv.at)\n* **ID Austria Support-Hotline:** 📞 [+43 1 71100-805555](tel:+43171100805555)\n* **Vor Ort:** Ihr zuständiges **Gemeindeamt** bzw. in Wien jedes **Magistratische Bezirksamt** (Servicetelefon 1360).\n\n💡 **Tipp:** Klicken Sie unten auf **\"Dossier für Beamtin/Beamten erstellen\"**, um eine druckfertige Zusammenfassung Ihres Falls ohne persönliche Daten herunterzuladen."
            : "### Official Human Support (Case Escalated to Public Service Desk)\n\nI have connected you with the official human representatives of the Austrian public administration. Your case has been noted in the **Civil Servant Portal** for human review.\n\n* **Federal Chancellery Citizen Service Helpdesk:**\n  * 📞 **Phone:** [+43 1 53115-0](tel:+431531150) (Monday to Friday, 08:00 – 16:00)\n  * ✉️ **Email:** [buergerservice@bka.gv.at](mailto:buergerservice@bka.gv.at)\n* **ID Austria Hotline:** 📞 [+43 1 71100-805555](tel:+43171100805555)\n* **Local Registry Offices:** Municipal town hall (*Gemeindeamt*) or any *Magistratisches Bezirksamt* in Vienna.\n\n💡 **Tip:** Click **\"Generate Civil Servant Dossier\"** below to create a copyable summary of your situation.";

        $this->addMessage([
            'id' => (string) Str::uuid(),
            'role' => 'assistant',
            'content' => $content,
            'timestamp' => now()->format('H:i'),
            'sources' => $sources,
            'is_human_request' => true,
        ]);

        $this->markConversationAsEscalated($reason ?? ($isGerman ? 'Bürgeranfrage: Persönliche Unterstützung / Beamtentelefon angefordert' : 'Citizen requested human caseworker assistance'));
    }

    public function generateDossier(): void
    {
        $isGerman = $this->locale === 'de';

        if ($this->activeWorkflow === 'passport') {
            $wf = new PassportWorkflow($this->passportState);
            $this->dossierContent = $wf->generateDossierSummary($this->locale);
        } elseif ($this->activeWorkflow === 'residence') {
            $wf = new ResidenceWorkflow($this->residenceState);
            $this->dossierContent = $wf->generateDossierSummary($this->locale);
        } else {
            $this->dossierContent = $this->buildEscalationDossier();
        }

        if ($this->conversationId) {
            try {
                Conversation::where('id', $this->conversationId)->update([
                    'case_dossier' => $this->dossierContent,
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

                    $this->messages[] = [
                        'id' => $id,
                        'role' => $m->role,
                        'content' => $content,
                        'timestamp' => $m->created_at?->format('H:i') ?? now()->format('H:i'),
                        'sources' => $m->sources ?? [],
                        'workflow' => $m->workflow,
                        'is_fallback' => (bool) $m->is_fallback,
                    ];

                    if ($m->workflow === 'officer_joined' || str_contains($content, 'ist dem Gespräch beigetreten') || str_contains($content, 'has joined the conversation')) {
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

    public function render(): View
    {
        $passportWf = new PassportWorkflow($this->passportState);
        $residenceWf = new ResidenceWorkflow($this->residenceState);

        return view('livewire.austrian-gov-assistant', [
            'passportChecklist' => $passportWf->getChecklist($this->locale),
            'passportFee' => $passportWf->getFeeInfo(),
            'residenceChecklist' => $residenceWf->getChecklist($this->locale),
            'residenceDeadline' => $residenceWf->getDeadlineStatus($this->locale),
        ]);
    }
}
