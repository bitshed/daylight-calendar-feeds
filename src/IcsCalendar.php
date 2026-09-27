<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Renders all-day events as an RFC 5545 iCalendar document.
 */
final readonly class IcsCalendar
{
    private const EOL = "\r\n";

    /** Lines longer than this many octets are folded, per RFC 5545 3.1. */
    private const MAX_LINE_OCTETS = 75;

    public function __construct(
        private string $name,
        private string $description,
        private DateTimeImmutable $generatedAt,
    ) {
    }

    /**
     * @param iterable<CalendarEvent> $events
     */
    public function render(iterable $events): string
    {
        $stamp = $this->generatedAt->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Bitshed//Daylight Calendar Feeds//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . self::escape($this->name),
            'X-WR-CALDESC:' . self::escape($this->description),
            'REFRESH-INTERVAL;VALUE=DURATION:P1D',
            'X-PUBLISHED-TTL:P1D',
        ];

        foreach ($events as $event) {
            array_push(
                $lines,
                'BEGIN:VEVENT',
                'UID:' . $event->uid,
                'DTSTAMP:' . $stamp,
                'DTSTART;VALUE=DATE:' . $event->date->format('Ymd'),
                'DTEND;VALUE=DATE:' . $event->date->modify('+1 day')->format('Ymd'),
                'SUMMARY:' . self::escape($event->summary),
                'DESCRIPTION:' . self::escape($event->description),
                'TRANSP:TRANSPARENT',
                'END:VEVENT',
            );
        }

        $lines[] = 'END:VCALENDAR';

        return implode(self::EOL, array_map(self::fold(...), $lines)) . self::EOL;
    }

    /**
     * Escapes a TEXT value (RFC 5545 3.3.11).
     */
    public static function escape(string $text): string
    {
        return strtr($text, [
            '\\' => '\\\\',
            ';' => '\\;',
            ',' => '\\,',
            "\r\n" => '\\n',
            "\n" => '\\n',
        ]);
    }

    /**
     * Folds a content line at 75 octets without splitting a UTF-8 character.
     */
    public static function fold(string $line): string
    {
        $folded = [];
        $current = '';
        // Continuation lines start with a space, which counts towards the limit.
        $limit = self::MAX_LINE_OCTETS;

        foreach (mb_str_split($line, 1, 'UTF-8') as $character) {
            if (strlen($current) + strlen($character) > $limit) {
                $folded[] = $current;
                $current = '';
                $limit = self::MAX_LINE_OCTETS - 1;
            }

            $current .= $character;
        }

        $folded[] = $current;

        return implode(self::EOL . ' ', $folded);
    }
}
