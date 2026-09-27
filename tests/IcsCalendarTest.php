<?php

declare(strict_types=1);

use Bitshed\Daylight\CalendarEvent;
use Bitshed\Daylight\IcsCalendar;

function renderCalendar(string $summary = 'A | B', string $description = "One\nTwo"): string
{
    $calendar = new IcsCalendar('Daylight: Test', 'Test, feed', new DateTimeImmutable('2026-09-27 10:00:00 UTC'));

    return $calendar->render([
        new CalendarEvent('20260927-test@example.com', new DateTimeImmutable('2026-09-27'), $summary, $description),
    ]);
}

it('writes CRLF line endings and keeps every line within 75 octets', function (): void {
    $ics = renderCalendar(str_repeat('Sunrise: 06:54:13 | ', 10));

    expect($ics)->toEndWith("END:VCALENDAR\r\n")
        ->and(str_replace("\r\n", '', $ics))->not->toContain("\n");

    foreach (explode("\r\n", rtrim($ics, "\r\n")) as $line) {
        expect(strlen($line))->toBeLessThanOrEqual(75);
    }
});

it('writes an all-day event ending the following day', function (): void {
    expect(renderCalendar())
        ->toContain("DTSTART;VALUE=DATE:20260927\r\n")
        ->toContain("DTEND;VALUE=DATE:20260928\r\n")
        ->toContain("DTSTAMP:20260927T100000Z\r\n")
        ->toContain("UID:20260927-test@example.com\r\n");
});

it('escapes text values', function (): void {
    expect(IcsCalendar::escape("a,b;c\\d\ne"))->toBe('a\\,b\;c\\\\d\\ne')
        ->and(renderCalendar())->toContain('X-WR-CALDESC:Test\\, feed');
});

it('folds without splitting a multibyte character', function (): void {
    $folded = IcsCalendar::fold('SUMMARY:' . str_repeat('é', 60));

    foreach (explode("\r\n", $folded) as $line) {
        expect(mb_check_encoding($line, 'UTF-8'))->toBeTrue()
            ->and(strlen($line))->toBeLessThanOrEqual(75);
    }

    expect(str_replace("\r\n ", '', $folded))->toBe('SUMMARY:' . str_repeat('é', 60));
});
