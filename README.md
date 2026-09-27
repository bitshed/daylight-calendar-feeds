# Daylight Calendar Feeds

Subscribable calendar feeds with one all-day event per day showing sunrise,
sunset, total daylight and how much daylight has been gained since the most
recent winter solstice.

```
Sunrise: 06:54:13 | Sunset: 19:06:37 | Extra 4h 38m 57s | Total Daylight: 12:12:23 | BST
```

The event notes repeat the figures one per line:

```
Sunrise: 06:54:13
Sunset: 19:06:37
Daylight: 12:12:23
Gained since solstice: 4h 38m 57s
Timezone: BST
```

Browse and subscribe at <https://daylight-calendar-feeds.bitshed.dev>.

## Requesting a location

[Open an issue](https://github.com/bitshed/daylight-calendar-feeds/issues) with
the place name, and its latitude and longitude if you know them.

## How it works

`bin/build` is a small PHP script with no runtime dependencies. For each
location in [`config.php`](config.php) it writes `dist/feeds/<slug>.ics`
covering the past week and the year ahead, then renders `dist/index.html`
listing every feed.

- Sunrise and sunset come from PHP's `date_sun_info()`: the moment the top
  edge of the sun crosses the horizon, allowing for refraction.
- "Gained" is today's daylight minus the daylight on the most recent winter
  solstice. The solstice is taken as the shortest day between the 17th and
  25th of December (or June, south of the equator), so the two figures come
  from the same model and agree to the second.
- Polar night and midnight sun are handled: the times show as `--:--:--` and
  daylight as `00:00:00` or `24:00:00`.

A GitHub Actions workflow rebuilds and deploys the site to GitHub Pages every
day at 00:30 UTC, and on every push to `main`.

## Adding a location

Append an entry to `locations` in `config.php`:

```php
[
    'slug' => 'bristol',              // becomes feeds/bristol.ics; never change it once published
    'name' => 'Bristol',
    'region' => 'England, United Kingdom',
    'latitude' => 51.4545,            // decimal degrees, north positive
    'longitude' => -2.5879,           // decimal degrees, east positive
    'timezone' => 'Europe/London',    // IANA identifier
],
```

## Development

Requires PHP 8.3 or later and Composer.

```bash
composer install
composer serve   # build into dist/ and preview at http://localhost:8000
composer build   # build into dist/ only
composer check   # PSR-12 (PHP_CodeSniffer), PHPStan at max level, Pest
```

## Deployment

1. In the repository's **Settings → Pages**, set **Source** to **GitHub Actions**.
2. Under **Custom domain**, enter `daylight-calendar-feeds.bitshed.dev` and save.
3. At the DNS provider for `bitshed.dev`, add a `CNAME` record for
   `daylight-calendar-feeds` pointing at `bitshed.github.io`. If the DNS is on
   Cloudflare, leave it **DNS only** (grey cloud) until GitHub has issued the
   certificate.
4. Once the DNS check passes, tick **Enforce HTTPS**.

GitHub disables scheduled workflows on public repositories after 60 days
without activity. If the feeds stop updating, re-enable the workflow from the
**Actions** tab.

## Licence

[MIT](LICENSE)
