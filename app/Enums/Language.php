<?php

namespace App\Enums;

/**
 * The single registry of languages Kompetent offers (CI chapter 14 "Mehrsprachigkeit").
 *
 * To add a language: add a case here (with its metadata below), add lang/{code}/chat.php and
 * lang/{code}/answer.php, and optionally resources/content/{code}/ for long texts. Everything else
 * (switcher, html lang/dir, prompt language, fallback chain) is derived from this enum.
 */
enum Language: string
{
    case German = 'de';
    case GermanSimple = 'de_simple';
    case English = 'en';
    case Turkish = 'tr';
    case Bosnian = 'bs';
    case Ukrainian = 'uk';
    case Romanian = 'ro';
    case Arabic = 'ar';

    public static function default(): self
    {
        return self::German;
    }

    public static function fromLocale(?string $locale): self
    {
        return self::tryFrom((string) $locale) ?? self::default();
    }

    public static function current(): self
    {
        return self::fromLocale(app()->getLocale());
    }

    /**
     * Name of the language in the language itself (CI: "Türkçe", not "Türkisch"; no flags).
     */
    public function nativeName(): string
    {
        return match ($this) {
            self::German => 'Deutsch',
            self::GermanSimple => 'Leichte Sprache',
            self::English => 'English',
            self::Turkish => 'Türkçe',
            self::Bosnian => 'Bosanski / Hrvatski / Srpski',
            self::Ukrainian => 'Українська',
            self::Romanian => 'Română',
            self::Arabic => 'العربية',
        };
    }

    /**
     * BCP 47 tag for the html lang attribute.
     */
    public function htmlLang(): string
    {
        return match ($this) {
            self::German, self::GermanSimple => 'de-AT',
            self::English => 'en-GB',
            self::Turkish => 'tr-TR',
            self::Bosnian => 'bs-BA',
            self::Ukrainian => 'uk-UA',
            self::Romanian => 'ro-RO',
            self::Arabic => 'ar',
        };
    }

    /**
     * Voice language for the browser's speech synthesis ("Vorlesen").
     */
    public function speechLang(): string
    {
        return match ($this) {
            self::Arabic => 'ar-SA',
            default => $this->htmlLang(),
        };
    }

    /**
     * @return 'ltr'|'rtl'
     */
    public function dir(): string
    {
        return $this->isRtl() ? 'rtl' : 'ltr';
    }

    public function isRtl(): bool
    {
        return $this === self::Arabic;
    }

    public function isSimple(): bool
    {
        return $this === self::GermanSimple;
    }

    /**
     * Languages to try for texts that are missing in this language, best first.
     * Factual content is never machine-translated: other languages fall back to English, then German.
     *
     * @return list<self>
     */
    public function contentChain(): array
    {
        return match ($this) {
            self::German => [self::German],
            self::GermanSimple => [self::GermanSimple, self::German],
            self::English => [self::English, self::German],
            default => [$this, self::English, self::German],
        };
    }

    /**
     * How the system prompt names the answer language.
     */
    public function promptName(): string
    {
        return match ($this) {
            self::German => 'German (Austrian standard German)',
            self::GermanSimple => 'German in "Leichte Sprache" (easy-to-read German)',
            self::English => 'English',
            self::Turkish => 'Turkish',
            self::Bosnian => 'Bosnian/Croatian/Serbian (Latin script)',
            self::Ukrainian => 'Ukrainian',
            self::Romanian => 'Romanian',
            self::Arabic => 'Arabic (Modern Standard Arabic)',
        };
    }

    /**
     * Style rules for answers in this language (CI chapter 07 "Tonalität und Sprache").
     */
    public function promptStyle(): string
    {
        return match ($this) {
            self::German => 'Use the formal "Sie" and Austrian German (Jänner, Meldezettel, Magistrat, heuer).',
            self::GermanSimple => 'Write in Leichte Sprache (CEFR A2): one sentence per line, one statement per sentence, numbers as digits, no foreign words, explain every official term. Use the formal "Sie".',
            self::English => 'Use plain, polite English.',
            default => 'Use the formal form of address of this language and plain, short sentences.',
        };
    }
}
