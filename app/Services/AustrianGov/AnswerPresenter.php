<?php

namespace App\Services\AustrianGov;

use App\Services\Citations\CitationMarkers;
use App\Services\Citations\CitedSource;

/**
 * Prepares one assistant message for the CI answer pattern (docs/ci, chapter 11):
 * clarification (phase 1) or answer blocks A steps · B documents · C sources · D next steps.
 *
 * The "Mit einer Person sprechen" button is not part of next_steps: the view always renders it last.
 */
class AnswerPresenter
{
    public const MAX_NEXT_STEPS = 3;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected static array $cache = [];

    /**
     * @param  array<string, mixed>  $msg
     * @param  bool  $isLastMessage  whether no message follows this one (clarification still awaits an answer)
     * @return array<string, mixed> kind ('clarify'|'answer'), label, clarify, awaiting_confirmation, blocks,
     *                              sources, cited_sources, further_sources, next_steps ({label, primary}), plain_text
     */
    public static function present(array $msg, string $locale, bool $isLastMessage): array
    {
        $content = (string) ($msg['content'] ?? '');
        $key = implode('|', [$msg['id'] ?? '', md5($content), md5((string) json_encode($msg['sources'] ?? [])), $locale, (int) $isLastMessage]);

        return self::$cache[$key] ??= self::build($msg, $content, $locale, $isLastMessage);
    }

    /**
     * @param  array<string, mixed>  $msg
     * @return array<string, mixed>
     */
    protected static function build(array $msg, string $content, string $locale, bool $isLastMessage): array
    {
        $parsed = MessageParser::parseWithNextSteps($content, $locale);

        $sources = [];
        foreach (array_values($msg['sources'] ?? []) as $index => $source) {
            $sources[] = CitedSource::fromArray((array) $source, $index + 1);
        }
        $validNumbers = array_map(fn (CitedSource $s) => $s->number, $sources);

        if ($parsed['kind'] === 'clarify') {
            $clarify = $parsed['clarify'] ?? ['intro' => '', 'intro_html' => '', 'items' => []];
            $items = $clarify['items'];

            return [
                'kind' => 'clarify',
                'label' => __('answer.label_clarify', [], $locale),
                'clarify' => $clarify,
                'awaiting_confirmation' => $isLastMessage,
                'blocks' => [],
                'sources' => [],
                'cited_sources' => [],
                'further_sources' => [],
                'next_steps' => [],
                'plain_text' => trim($clarify['intro']."\n".implode("\n", array_map(fn ($item, $n) => ($n + 1).'. '.$item, $items, array_keys($items)))),
            ];
        }

        $anchor = 'src-'.($msg['id'] ?? 'msg');
        $usedNumbers = [];
        $stepCount = 0;
        $blocks = [];

        foreach ($parsed['blocks'] as $block) {
            if ($block['type'] === 'prose') {
                $block['sources'] = array_values(array_intersect($block['sources'] ?? [], $validNumbers));
                $block['html'] = CitationMarkers::linkifyHtml($block['html'], $validNumbers, $anchor, $locale);
                $usedNumbers = [...$usedNumbers, ...$block['sources']];
            } else {
                foreach ($block['items'] as $i => $item) {
                    $block['items'][$i]['sources'] = array_values(array_intersect($item['sources'] ?? [], $validNumbers));
                    $usedNumbers = [...$usedNumbers, ...$block['items'][$i]['sources']];
                }

                if ($block['type'] === 'steps') {
                    $stepCount += count($block['items']);
                }
            }

            $blocks[] = $block;
        }

        $usedNumbers = array_unique($usedNumbers);
        $cited = array_values(array_filter($sources, fn (CitedSource $s) => in_array($s->number, $usedNumbers, true)));
        usort($cited, fn (CitedSource $a, CitedSource $b) => $a->number <=> $b->number);

        $nextSteps = array_slice(array_values(array_filter($parsed['next_steps'], fn (string $step) => ! app(IntentMatcher::class)->wantsHuman($step))), 0, self::MAX_NEXT_STEPS);
        $nextSteps = array_map(fn (string $step, int $i) => ['label' => $step, 'primary' => $i === 0], $nextSteps, array_keys($nextSteps));

        $label = $stepCount > 0
            ? trans_choice('answer.label_steps', $stepCount, ['count' => $stepCount], $locale)
            : __('answer.label', [], $locale);

        return [
            'kind' => 'answer',
            'label' => $label,
            'clarify' => null,
            'awaiting_confirmation' => false,
            'blocks' => $blocks,
            'sources' => array_map(fn (CitedSource $s) => $s->toArray(), $sources),
            'cited_sources' => array_map(fn (CitedSource $s) => $s->toArray(), $cited),
            'further_sources' => $cited === [] ? array_map(fn (CitedSource $s) => $s->toArray(), $sources) : [],
            'next_steps' => $nextSteps,
            'plain_text' => self::plainText($content),
        ];
    }

    /**
     * Text for read-aloud and copy: no source markers, no clarify marker, no next-steps block.
     */
    public static function plainText(string $content): string
    {
        $content = (string) preg_replace('/^\s*\[(?:RÜCKFRAGE|RUECKFRAGE|CLARIFY|ANTWORT|ANSWER)\]\s*$/imu', '', $content);
        $content = (string) preg_replace('/\[(?:NÄCHSTE SCHRITTE|NEXT STEPS)\][\s\S]*$/iu', '', $content);

        return trim(CitationMarkers::strip($content));
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}
