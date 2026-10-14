<?php

namespace App\Services\AustrianGov;

/**
 * Recognises messages written by staff in the employee portal.
 *
 * New messages carry the workflow flag; the text checks only cover messages stored before the flag existed.
 */
class OfficerMessages
{
    public const JOINED = 'officer_joined';

    public const MESSAGE = 'officer_message';

    /**
     * @param  array{workflow?: ?string, content: string}  $message
     * @return 'joined'|'message'|null
     */
    public static function kind(array $message): ?string
    {
        $workflow = $message['workflow'] ?? null;
        $content = $message['content'];

        if ($workflow === self::JOINED || str_contains($content, 'ist dem Gespräch beigetreten') || str_contains($content, 'has joined the conversation')) {
            return 'joined';
        }

        if ($workflow === self::MESSAGE || str_contains($content, 'Offizielle Mitteilung der Sachbearbeitung')) {
            return 'message';
        }

        return null;
    }
}
