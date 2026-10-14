<?php

namespace App\Services\Content;

use App\Enums\Language;

/**
 * A content text and the language it is actually served in (may differ from the requested one).
 */
final readonly class ResolvedContent
{
    public function __construct(
        public string $body,
        public Language $servedIn,
    ) {}
}
