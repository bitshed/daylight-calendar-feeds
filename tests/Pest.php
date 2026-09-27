<?php

declare(strict_types=1);

use Bitshed\Daylight\Location;

function location(
    string $slug = 'london',
    float $latitude = 51.5072,
    float $longitude = -0.1276,
    string $timezone = 'Europe/London',
): Location {
    return Location::fromArray([
        'slug' => $slug,
        'name' => ucfirst($slug),
        'region' => 'Somewhere',
        'latitude' => $latitude,
        'longitude' => $longitude,
        'timezone' => $timezone,
    ]);
}
