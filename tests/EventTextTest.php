<?php

declare(strict_types=1);

use Bitshed\Daylight\DayReport;
use Bitshed\Daylight\EventText;

function reportAt(?string $sunrise, ?string $sunset, int $daylight, int $gained): DayReport
{
    $location = location();
    $at = static fn (?string $time): ?DateTimeImmutable => $time === null
        ? null
        : new DateTimeImmutable('2026-03-20 ' . $time, $location->timezone);

    return new DayReport(
        location: $location,
        date: new DateTimeImmutable('2026-09-20', $location->timezone),
        sunrise: $at($sunrise),
        sunset: $at($sunset),
        daylightSeconds: $daylight,
        gainedSeconds: $gained,
        solstice: new DateTimeImmutable('2025-12-21', $location->timezone),
    );
}

it('formats the event title exactly as specified', function (): void {
    $report = reportAt('06:54:13', '19:06:37', 12 * 3600 + 12 * 60 + 23, 4 * 3600 + 38 * 60 + 57);

    expect(EventText::title($report))->toBe(
        'Sunrise: 06:54:13 | Sunset: 19:06:37 | Extra 4h 38m 57s | Total Daylight: 12:12:23 | BST',
    );
});

it('formats the event notes exactly as specified', function (): void {
    $report = reportAt('06:54:13', '19:06:37', 12 * 3600 + 12 * 60 + 23, 4 * 3600 + 38 * 60 + 57);

    expect(EventText::description($report))->toBe(implode("\n", [
        'Sunrise: 06:54:13',
        'Sunset: 19:06:37',
        'Daylight: 12:12:23',
        'Gained since solstice: 4h 38m 57s',
        'Timezone: BST',
    ]));
});

it('shows placeholders when the sun does not rise or set', function (): void {
    expect(EventText::title(reportAt(null, null, 86400, 86400)))
        ->toStartWith('Sunrise: --:--:-- | Sunset: --:--:-- | Extra 24h 0m 0s | Total Daylight: 24:00:00');
});

it('formats durations', function (int $seconds, string $duration, string $clock): void {
    expect(EventText::duration($seconds))->toBe($duration)
        ->and(EventText::clock(abs($seconds)))->toBe($clock);
})->with([
    [0, '0h 0m 0s', '00:00:00'],
    [59, '0h 0m 59s', '00:00:59'],
    [3661, '1h 1m 1s', '01:01:01'],
    [-75, '-0h 1m 15s', '00:01:15'],
]);
