<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use DateTimeImmutable;

/**
 * Formats a day's figures into the event title and notes.
 *
 * Title: Sunrise: 06:54:13 | Sunset: 19:06:37 | Extra 4h 38m 57s | Total Daylight: 12:12:23 | BST
 */
final class EventText
{
    private const NO_TIME = '--:--:--';

    public static function title(DayReport $report): string
    {
        return implode(' | ', [
            'Sunrise: ' . self::time($report->sunrise),
            'Sunset: ' . self::time($report->sunset),
            'Extra ' . self::duration($report->gainedSeconds),
            'Total Daylight: ' . self::clock($report->daylightSeconds),
            $report->timezoneAbbreviation(),
        ]);
    }

    public static function description(DayReport $report): string
    {
        return implode("\n", [
            'Sunrise: ' . self::time($report->sunrise),
            'Sunset: ' . self::time($report->sunset),
            'Daylight: ' . self::clock($report->daylightSeconds),
            'Gained since solstice: ' . self::duration($report->gainedSeconds),
            'Timezone: ' . $report->timezoneAbbreviation(),
        ]);
    }

    /**
     * A length of time as "4h 38m 57s". Negative values keep a leading minus,
     * which only happens near the equator where day length barely moves.
     */
    public static function duration(int $seconds): string
    {
        $sign = $seconds < 0 ? '-' : '';
        $seconds = abs($seconds);

        return sprintf('%s%dh %dm %ds', $sign, intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    /**
     * A length of time as "12:12:23".
     */
    public static function clock(int $seconds): string
    {
        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    private static function time(?DateTimeImmutable $moment): string
    {
        return $moment?->format('H:i:s') ?? self::NO_TIME;
    }
}
