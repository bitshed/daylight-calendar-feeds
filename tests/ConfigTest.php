<?php

declare(strict_types=1);

use Bitshed\Daylight\Config;

function configData(array $locations): array
{
    return [
        'site' => [
            'title' => 'Test',
            'base_url' => 'https://example.com/',
            'repository_url' => 'https://github.com/example/repo',
            'issues_url' => 'https://github.com/example/repo/issues',
        ],
        'feed' => ['past_days' => 1, 'future_days' => 2],
        'locations' => $locations,
    ];
}

function locationData(array $overrides = []): array
{
    return [
        'slug' => 'london',
        'name' => 'London',
        'region' => 'England',
        'latitude' => 51.5,
        'longitude' => -0.12,
        'timezone' => 'Europe/London',
        ...$overrides,
    ];
}

it('loads the shipped config.php', function (): void {
    $config = Config::load(dirname(__DIR__) . '/config.php');

    expect($config->locations)->not->toBeEmpty()
        ->and($config->baseUrl)->not->toEndWith('/');
});

it('builds feed URLs from the base URL and slug', function (): void {
    $config = Config::fromArray(configData([locationData()]));

    expect($config->feedUrl($config->locations[0]))->toBe('https://example.com/feeds/london.ics');
});

it('rejects duplicate slugs', function (): void {
    Config::fromArray(configData([locationData(), locationData()]));
})->throws(InvalidArgumentException::class, 'Duplicate location slugs: london.');

it('rejects invalid locations', function (array $overrides, string $message): void {
    expect(fn () => Config::fromArray(configData([locationData($overrides)])))
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'slug with spaces' => [['slug' => 'New York'], 'must be lowercase'],
    'unknown timezone' => [['timezone' => 'Mars/Olympus'], 'Unknown timezone'],
    'latitude out of range' => [['latitude' => 91], 'Latitude'],
    'latitude as string' => [['latitude' => '51.5'], 'must be a number'],
    'missing name' => [['name' => ''], 'non-empty string'],
]);
