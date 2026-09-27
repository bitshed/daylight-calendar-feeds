<?php

declare(strict_types=1);

use Bitshed\Daylight\Config;
use Bitshed\Daylight\DaylightCalculator;
use Bitshed\Daylight\FeedBuilder;
use Bitshed\Daylight\SiteBuilder;

beforeEach(function (): void {
    $this->output = sys_get_temp_dir() . '/daylight-' . bin2hex(random_bytes(4));
    $this->config = Config::fromArray(configData([
        locationData(),
        locationData(['slug' => 'glasgow', 'name' => 'Glasgow & Clyde', 'latitude' => 55.86, 'longitude' => -4.25]),
    ]));

    $root = dirname(__DIR__);
    $this->written = (new SiteBuilder(
        config: $this->config,
        feeds: new FeedBuilder($this->config, new DaylightCalculator()),
        templateDirectory: $root . '/templates',
        assetDirectory: $root . '/assets',
    ))->build($this->output, new DateTimeImmutable('2026-09-27 01:00 UTC'));
});

afterEach(function (): void {
    exec('rm -rf ' . escapeshellarg($this->output));
});

it('writes one feed per location plus the page and its assets', function (): void {
    expect($this->written)->toBe([
        'feeds/london.ics',
        'feeds/glasgow.ics',
        'index.html',
        'favicon.svg',
        'site.css',
    ]);

    foreach ($this->written as $path) {
        expect(is_file($this->output . '/' . $path))->toBeTrue();
    }
});

it('covers past_days before to future_days after the build date', function (): void {
    $ics = (string) file_get_contents($this->output . '/feeds/london.ics');

    expect(substr_count($ics, 'BEGIN:VEVENT'))->toBe(4)
        ->and($ics)->toContain('DTSTART;VALUE=DATE:20260926')
        ->and($ics)->toContain('DTSTART;VALUE=DATE:20260929')
        ->and($ics)->toContain('UID:20260927-london@example.com');
});

it('lists every feed on the page with escaped names', function (): void {
    $html = (string) file_get_contents($this->output . '/index.html');

    expect($html)
        ->toContain('webcal://example.com/feeds/london.ics')
        ->toContain('https://example.com/feeds/glasgow.ics')
        ->toContain('Glasgow &amp; Clyde')
        ->toContain('https://github.com/example/repo/issues')
        ->not->toContain('Glasgow & Clyde');
});
