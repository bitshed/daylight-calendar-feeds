<?php

declare(strict_types=1);

use Bitshed\Daylight\DaylightCalculator;

beforeEach(function (): void {
    $this->calculator = new DaylightCalculator();
});

it('matches published sun times for London to within a minute', function (): void {
    // Published figures for 27 September 2026: sunrise 06:55, sunset 18:48 BST.
    $report = $this->calculator->report(location(), new DateTimeImmutable('2026-09-27'));
    $expected = static fn (string $time): int => (new DateTimeImmutable('2026-09-27 ' . $time, location()->timezone))
        ->getTimestamp();

    expect(abs($report->sunrise?->getTimestamp() - $expected('06:55')))->toBeLessThanOrEqual(60)
        ->and(abs($report->sunset?->getTimestamp() - $expected('18:48')))->toBeLessThanOrEqual(60)
        ->and($report->daylightSeconds)->toBe($report->sunset?->getTimestamp() - $report->sunrise?->getTimestamp());
});

it('reports zero gained on the winter solstice itself', function (): void {
    $report = $this->calculator->report(location(), new DateTimeImmutable('2025-12-21'));

    expect($report->gainedSeconds)->toBe(0)
        ->and($report->solstice->format('Y-m-d'))->toBe('2025-12-21');
});

it('measures from the previous year until this year\'s solstice has passed', function (): void {
    expect($this->calculator->report(location(), new DateTimeImmutable('2026-12-20'))->solstice->format('Y-m-d'))
        ->toBe('2025-12-21')
        ->and($this->calculator->report(location(), new DateTimeImmutable('2026-12-25'))->solstice->format('Y-m-d'))
        ->toBe('2026-12-21');
});

it('never reports negative gain away from the equator', function (): void {
    $day = new DateTimeImmutable('2026-01-01');

    for ($i = 0; $i < 366; $i++) {
        expect($this->calculator->report(location(), $day->modify("+{$i} days"))->gainedSeconds)
            ->toBeGreaterThanOrEqual(0);
    }
});

it('uses the June solstice in the southern hemisphere', function (): void {
    $sydney = location('sydney', -33.8688, 151.2093, 'Australia/Sydney');
    $report = $this->calculator->report($sydney, new DateTimeImmutable('2026-09-27'));

    expect($report->solstice->format('m'))->toBe('06')
        ->and($report->gainedSeconds)->toBeGreaterThan(0)
        ->and($report->timezoneAbbreviation())->toBe('AEST');
});

it('handles polar night and midnight sun', function (): void {
    $tromso = location('tromso', 69.6492, 18.9553, 'Europe/Oslo');
    $night = $this->calculator->report($tromso, new DateTimeImmutable('2026-12-21'));
    $day = $this->calculator->report($tromso, new DateTimeImmutable('2026-06-21'));

    expect($night->sunrise)->toBeNull()
        ->and($night->daylightSeconds)->toBe(0)
        ->and($day->sunrise)->toBeNull()
        ->and($day->daylightSeconds)->toBe(86400)
        ->and($day->gainedSeconds)->toBe(86400);
});

it('switches timezone abbreviation across the clock change', function (): void {
    expect($this->calculator->report(location(), new DateTimeImmutable('2026-03-28'))->timezoneAbbreviation())
        ->toBe('GMT')
        ->and($this->calculator->report(location(), new DateTimeImmutable('2026-03-29'))->timezoneAbbreviation())
        ->toBe('BST');
});

it('reports on the local calendar day whatever timezone the input is in', function (): void {
    // 23:30 UTC on the 27th is already the 28th in Sydney.
    $sydney = location('sydney', -33.8688, 151.2093, 'Australia/Sydney');
    $report = $this->calculator->report(
        $sydney,
        new DateTimeImmutable('2026-09-28 09:30', new DateTimeZone('Australia/Sydney')),
    );

    expect($report->date->format('Y-m-d'))->toBe('2026-09-28')
        ->and($report->sunrise?->format('Y-m-d'))->toBe('2026-09-28');
});
