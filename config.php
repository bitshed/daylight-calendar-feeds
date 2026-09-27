<?php

declare(strict_types=1);

/*
 * Site and feed configuration.
 *
 * To add a feed, append an entry to "locations". The slug becomes the feed's
 * file name (feeds/<slug>.ics), so keep it lowercase and hyphenated, and never
 * change it once published: subscribers' calendars point at that URL.
 *
 * Coordinates are decimal degrees (north and east positive). The timezone must
 * be a valid IANA identifier, and decides both the displayed times and the
 * abbreviation shown at the end of each event title.
 */

return [
    'site' => [
        'title' => 'Daylight Calendar Feeds',
        'base_url' => 'https://daylight.bitshed.dev',
        'repository_url' => 'https://github.com/bitshed/daylight-calendar-feeds',
        'issues_url' => 'https://github.com/bitshed/daylight-calendar-feeds/issues',
    ],

    'feed' => [
        // How many days either side of the build date each feed contains.
        'past_days' => 7,
        'future_days' => 365,
    ],

    'locations' => [
        [
            'slug' => 'stoke-on-trent',
            'name' => 'Stoke-on-Trent',
            'region' => 'England, United Kingdom',
            'latitude' => 53.0027,
            'longitude' => -2.1794,
            'timezone' => 'Europe/London',
        ],
        [
            'slug' => 'manchester',
            'name' => 'Manchester',
            'region' => 'England, United Kingdom',
            'latitude' => 53.4808,
            'longitude' => -2.2426,
            'timezone' => 'Europe/London',
        ],
        [
            'slug' => 'london',
            'name' => 'London',
            'region' => 'England, United Kingdom',
            'latitude' => 51.5072,
            'longitude' => -0.1276,
            'timezone' => 'Europe/London',
        ],
        [
            'slug' => 'glasgow',
            'name' => 'Glasgow',
            'region' => 'Scotland, United Kingdom',
            'latitude' => 55.8642,
            'longitude' => -4.2518,
            'timezone' => 'Europe/London',
        ],
        [
            'slug' => 'liverpool',
            'name' => 'Liverpool',
            'region' => 'England, United Kingdom',
            'latitude' => 53.4084,
            'longitude' => -2.9916,
            'timezone' => 'Europe/London',
        ],
    ],
];
