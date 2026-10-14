# Translations

Kompetent offers every language registered in `app/Enums/Language.php`. That enum is the single place that defines a language (code, native name, `lang`/`dir`, prompt language and fallback chain). The switcher, the `<html lang dir>` attributes and the system prompt are all derived from it.

## Files per language

| File | Content | Which languages |
| --- | --- | --- |
| `chat.php` | Interface texts of the citizen chat | all |
| `answer.php` | Labels of the CI answer pattern (Rückfrage, steps, documents, sources, next steps) | all |
| `assistant.php` | Messages the assistant writes itself (welcome, guided workflows, human support) | reviewed only (de, en) |
| `workflows.php` | Passport and residence workflows: checklists, deadlines, dossiers | reviewed only (de, en) |
| `sources.php` | Titles and descriptions of official sources (ids in `resources/content/sources.php`) | reviewed only (de, en) |
| `fallbacks.php` | Checklists of the stored fallback answers | reviewed only (de, en) |

Factual content (fees, deadlines, documents) is never machine-translated. Languages without their own factual files fall back along `Language::contentChain()` (e.g. Türkçe → English → Deutsch, Leichte Sprache → Deutsch). The chat then shows a note saying which language the content is in.

## Adding a language

1. Add a case to `app/Enums/Language.php` and fill in its metadata.
2. Copy `lang/en/chat.php` and `lang/en/answer.php` into `lang/{code}/` and translate them. Keep every key and every `:placeholder`.
3. Use the plural forms of the language for `answer.label_steps`.
4. Optional, after a factual review: add `assistant.php`, `workflows.php`, `sources.php`, `fallbacks.php` and `resources/content/{code}/`.
5. Run `php artisan test --filter=Translation`. The completeness test checks keys, placeholders and plural forms.

## Review status

The texts in `tr`, `bs`, `uk`, `ro`, `ar` and `de_simple` were written by the project team. **They must be reviewed by native speakers before going live.** `de` and `en` are the source languages.
