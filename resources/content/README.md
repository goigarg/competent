# Content

Long texts that are not interface strings. They are read by `App\Services\Content\ContentRepository`.

| Path | Content |
| --- | --- |
| `prompt/system.md` | System prompt: persona and CI answer pattern, in English, the same for every language. The answer language is added per request from `App\Enums\Language`. |
| `prompt/facts.md` | Austrian administrative facts in German with the official terms. The same for every language and never translated. |
| `sources.php` | Official sources per topic (id, url, tag). Titles and descriptions are in `lang/{locale}/sources.php`. |
| `intents.php` | Keywords of all languages for "talk to a person", dossier and the guided workflows. |
| `{locale}/fallbacks/*.md` | Stored answers when the AI service is unavailable, one per topic. |

## Languages and fallback

Files under `{locale}/` exist only for reviewed languages (currently `de` and `en`). Other languages are served the first available version in `Language::contentChain()`, and the chat says which language that is.

Facts (fees, deadlines, documents) are only added after a factual review and are never machine-translated.

A snapshot test (`tests/Feature/ContentSnapshotTest.php`) freezes the German and English output. Regenerate it only for intended content changes, with `UPDATE_CONTENT_SNAPSHOT=1 php artisan test --filter=ContentSnapshot`.
