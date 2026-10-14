<?php

namespace App\Services\AustrianGov;

use App\Models\KnowledgeNugget;
use App\Services\Ai\Contracts\AiAssistantInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KnowledgeDistiller
{
    public function __construct(protected AiAssistantInterface $aiClient) {}

    /**
     * Inspect a citizen inquiry and AI response to extract and save a new knowledge nugget if novel.
     */
    public function learnFromInteraction(string $userPrompt, string $aiResponse, string $locale = 'de'): ?KnowledgeNugget
    {
        // Don't distill small talk, very short queries, or workflow buttons
        if (mb_strlen(trim($userPrompt)) < 15 || mb_strlen(trim($aiResponse)) < 100) {
            return null;
        }

        // Check if we already have a strong match in the database
        $existing = KnowledgeNugget::findRelevantNuggets($userPrompt, 1);
        if ($existing->isNotEmpty()) {
            return null; // Already covered by existing knowledge nugget!
        }

        // Extract a clean factual takeaway
        try {
            $prompt = <<<PROMPT
Analysiere die folgende Behörden-Frage und die dazugehörige Antwort.
Extrahiere daraus eine prägnante, rein sachliche Wissensnotiz ("Knowledge Nugget") für österreichische Behörden.

Benutzer-Frage: "{$userPrompt}"
Antwort: "{$aiResponse}"

Antworte STRIKT als JSON im folgenden Format:
{
  "domain": "one of: passport, residence, permit, work_permit, tax, health, driving_vehicle, family, employment, citizenship",
  "topic": "a_snake_case_topic_slug",
  "title": "A short descriptive title in German or English",
  "keywords": ["keyword1", "keyword2", "keyword3"],
  "factual_context": "Bullet points with the exact Austrian laws, fees, deadlines, or required forms without personal references."
}
PROMPT;

            $res = $this->aiClient->chat([
                ['role' => 'system', 'content' => 'Du bist ein KI-System zur Wissensextraktion für die österreichische Bundesverwaltung. Antworte ausschließlich mit gültigem JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ], [
                'temperature' => 0.1,
                'max_tokens' => 800,
            ]);

            if (! $res['success'] || empty(trim($res['content']))) {
                return null;
            }

            // Extract JSON
            $content = trim($res['content']);
            if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                $content = $matches[0];
            }

            $data = json_decode($content, true);
            if (! is_array($data) || empty($data['factual_context']) || empty($data['topic'])) {
                return null;
            }

            $topicSlug = Str::slug($data['topic'], '_');
            if (empty($topicSlug)) {
                $topicSlug = 'topic_'.Str::random(6);
            }

            return KnowledgeNugget::create([
                'domain' => $data['domain'] ?? 'general',
                'topic' => $topicSlug,
                'title' => $data['title'] ?? 'Österreichische Verwaltungsnotiz',
                'keywords' => $data['keywords'] ?? [mb_strtolower($userPrompt)],
                'factual_context' => $data['factual_context'],
                'official_source_url' => 'https://www.oesterreich.gv.at',
                'is_verified' => true,
                'hit_count' => 1,
            ]);
        } catch (\Throwable $e) {
            Log::info('Knowledge distillation skipped: '.$e->getMessage());

            return null;
        }
    }
}
