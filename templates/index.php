<?php

declare(strict_types=1);

use Bitshed\Daylight\Config;
use Bitshed\Daylight\DayReport;
use Bitshed\Daylight\EventText;
use Bitshed\Daylight\Location;

/**
 * @var Config $config
 * @var list<array{location: Location, url: string, webcal: string, path: string, today: DayReport}> $feeds
 * @var DateTimeImmutable $generatedAt
 * @var Closure(string): string $e
 */

$sample = $feeds[0]['today'] ?? null;
$time = static fn (?DateTimeImmutable $moment): string => $moment?->format('H:i:s') ?? '--:--:--';
$description = 'Free calendar feeds that add one all-day event per day with sunrise, sunset, total daylight '
    . 'and how much daylight has come back since the winter solstice.';
?>
<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $e($config->title) ?> — Bitshed</title>
    <meta name="description" content="<?= $e($description) ?>">
    <link rel="canonical" href="<?= $e($config->baseUrl) ?>/">
    <meta property="og:site_name" content="Bitshed">
    <meta property="og:locale" content="en_GB">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= $e($config->title) ?>">
    <meta property="og:description" content="<?= $e($description) ?>">
    <meta property="og:url" content="<?= $e($config->baseUrl) ?>/">
    <meta name="twitter:card" content="summary">
    <script>try{var t=localStorage.getItem('theme');if(t)document.documentElement.dataset.theme=t}catch(e){}</script>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap">
    <link rel="stylesheet" href="/site.css?v=<?= $e($generatedAt->format('Ymd')) ?>">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="wrap">
    <header class="bar">
        <a class="id" href="https://bitshed.dev" rel="noopener">
            <svg class="mark" viewBox="0 0 112 132" role="img" aria-label="Bitshed">
                <path d="M8 66 L56 18 L104 66" fill="none" stroke="var(--logo-ink)" stroke-width="10" stroke-linecap="square" stroke-linejoin="miter"/>
                <path d="M19 62 V122 H93 V62 L56 31 Z" fill="var(--logo-amber)" stroke="var(--logo-ink)" stroke-width="7"/>
                <path d="M28 70 H84 M28 85 H84 M28 100 H84" stroke="var(--logo-amber-dark)" stroke-width="3" opacity=".65"/>
                <path d="M56 32 V52" stroke="var(--logo-ink)" stroke-width="4"/>
                <circle cx="56" cy="56" r="7" fill="var(--logo-ink)"/>
                <path d="M48 56 H64" stroke="var(--logo-paper)" stroke-width="3"/>
                <rect x="30" y="92" width="27" height="18" rx="2" fill="var(--logo-ink)"/>
                <path d="M38 97 l-5 4 5 4 M49 97 l5 4 -5 4 M46 95 l-6 12" fill="none" stroke="var(--logo-paper)" stroke-width="2.6"/>
                <rect x="62" y="83" width="18" height="27" rx="2" fill="var(--logo-ink)" opacity=".92"/>
                <circle cx="67" cy="89" r="2" fill="var(--logo-amber)"/><circle cx="75" cy="89" r="2" fill="var(--logo-amber)"/>
                <path d="M66 95 V104 M75 95 V104" stroke="var(--logo-amber)" stroke-width="3"/>
                <path d="M24 111 H88" stroke="var(--logo-ink)" stroke-width="6"/>
                <path d="M31 114 V125 M80 114 V125" stroke="var(--logo-ink)" stroke-width="5"/>
                <path d="M5 126 H107" stroke="var(--logo-ink)" stroke-width="5" stroke-linecap="round"/>
                <path d="M15 125 q-3-15 -10-19 q10 1 14 11 q0-13 7-18 q-1 15 3 26" fill="var(--logo-grass)"/>
                <path d="M97 125 q3-15 10-19 q-10 1-14 11 q0-13-7-18 q1 15-3 26" fill="var(--logo-grass)"/>
            </svg>
            <span class="wm">
                <b>Bitshed</b>
                <span class="dot" aria-hidden="true"></span>
                <span class="tld">daylight</span>
            </span>
        </a>
        <nav>
            <a href="<?= $e($config->repositoryUrl) ?>" rel="noopener">GitHub &rarr;</a>
        </nav>
        <div class="ctl">
            <button class="tt" type="button" data-tt aria-label="Switch between light and dark"><svg viewBox="0 0 16 16" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"><path d="M13.6 9.9A5.9 5.9 0 0 1 6.1 2.4a5.9 5.9 0 1 0 7.5 7.5Z"/></svg></button>
        </div>
    </header>

    <main id="main">
        <section class="head">
            <h1>Watch the light come back, <em>one day at a time</em>.</h1>
            <p>Calendar feeds that put a single all-day event on every day: when the sun rises and sets where you are, how long it stays up, and how much daylight you have gained since the shortest day of the year. Subscribe once and it keeps itself up to date.</p>
        </section>

        <section class="sec" id="feeds">
            <div class="sechead">
                <p class="lbl">Feeds</p>
                <span class="lbl"><?= count($feeds) ?> <?= count($feeds) === 1 ? 'location' : 'locations' ?></span>
            </div>
            <div class="feeds">
<?php foreach ($feeds as $feed) : ?>
    <?php $today = $feed['today']; ?>
                <article class="feed">
                    <div class="top">
                        <h3><?= $e($feed['location']->name) ?></h3>
                        <span class="rg"><?= $e($feed['location']->region) ?></span>
                    </div>
                    <dl class="figs" aria-label="<?= $e($feed['location']->name) ?> on <?= $e($today->date->format('j F Y')) ?>">
                        <div><dt>Sunrise</dt><dd><?= $e($time($today->sunrise)) ?></dd></div>
                        <div><dt>Sunset</dt><dd><?= $e($time($today->sunset)) ?></dd></div>
                        <div><dt>Daylight</dt><dd><?= $e(EventText::clock($today->daylightSeconds)) ?></dd></div>
                        <div class="gain"><dt>Gained</dt><dd><?= $e(EventText::duration($today->gainedSeconds)) ?></dd></div>
                    </dl>
                    <div class="acts">
                        <a class="btn primary" href="<?= $e($feed['webcal']) ?>">Subscribe</a>
                        <button class="btn" type="button" data-copy="<?= $e($feed['url']) ?>">Copy URL</button>
                        <a class="btn" href="/<?= $e($feed['path']) ?>" download>.ics</a>
                    </div>
                    <span class="url"><?= $e($feed['url']) ?></span>
                </article>
<?php endforeach; ?>
            </div>
<?php if ($sample !== null) : ?>
            <p class="prose" style="margin-top:14px">Figures above are for <?= $e($sample->date->format('l j F Y')) ?>, local time.</p>
<?php endif; ?>
        </section>

<?php if ($sample !== null) : ?>
        <section class="sec gap" id="event">
            <div class="sechead">
                <p class="lbl">What each event shows</p>
                <span class="lbl">One per day</span>
            </div>
            <h2>A glance at the title tells you the day</h2>
            <div class="prose">
                <p>Every day in the feed gets one all-day event. The title carries the numbers so you can read them straight from a month view, and the notes repeat them one per line. Here is today's event for <?= $e($sample->location->name) ?>:</p>
            </div>
            <figure class="event">
                <div class="ttl"><span class="when"><?= $e($sample->date->format('l j F')) ?> · All day</span><?= $e(EventText::title($sample)) ?></div>
                <pre><?= $e(EventText::description($sample)) ?></pre>
            </figure>
            <dl class="fields">
                <dt>Sunrise / Sunset</dt>
                <dd>When the top edge of the sun crosses the horizon, allowing for atmospheric refraction, to the second.</dd>
                <dt>Total Daylight</dt>
                <dd>The time between sunrise and sunset, as hours, minutes and seconds.</dd>
                <dt>Extra</dt>
                <dd>How much longer today is than the most recent winter solstice at that location. It climbs from zero each December to its peak at the summer solstice, then falls back towards zero as the nights draw in.</dd>
                <dt>BST / GMT</dt>
                <dd>The timezone the times are shown in, so the clock change in spring and autumn is never a surprise.</dd>
            </dl>
        </section>
<?php endif; ?>

        <section class="sec gap" id="subscribe">
            <div class="sechead">
                <p class="lbl">Subscribing</p>
                <span class="lbl">Refreshed daily</span>
            </div>
            <h2>Add it once, and it stays current</h2>
            <div class="prose">
                <p>The feeds are rebuilt every day and always cover the past week and the year ahead. Subscribe rather than importing the file, so your calendar picks up each rebuild on its own.</p>
            </div>
            <div class="band">
                <div>
                    <h4>Apple Calendar</h4>
                    <p>Press <b>Subscribe</b> on a feed. On a Mac or iPhone it opens Calendar and asks to confirm.</p>
                </div>
                <div>
                    <h4>Google Calendar</h4>
                    <p><b>Copy URL</b>, then in Google Calendar on the web choose <b>Other calendars → + → From URL</b> and paste it.</p>
                </div>
                <div>
                    <h4>Outlook</h4>
                    <p><b>Copy URL</b>, then choose <b>Add calendar → Subscribe from web</b> and paste it.</p>
                </div>
            </div>
            <div class="prose" style="margin-top:14px">
                <p>Each calendar app decides how often it checks for updates. Apple Calendar can be set to refresh daily; Google Calendar checks on its own schedule, which can take a day or so.</p>
            </div>
        </section>

        <section class="sec gap" id="request">
            <div class="sechead">
                <p class="lbl">Missing your town?</p>
                <span class="lbl">Open source</span>
            </div>
            <div class="ask">
                <p><b>Request a new feed</b> by opening an issue on GitHub. Tell us the place name, and its latitude and longitude if you know them.</p>
                <a class="btn primary" href="<?= $e($config->issuesUrl) ?>" rel="noopener">Open an issue &rarr;</a>
            </div>
            <div class="prose" style="margin-top:14px">
                <p>The feeds are built by a small PHP script on a daily schedule and served as static files. The source, and the list of locations in <code>config.php</code>, is on <a href="<?= $e($config->repositoryUrl) ?>" rel="noopener">GitHub</a>.</p>
            </div>
        </section>
    </main>

    <footer class="foot">
        <div class="links">
            <a href="https://bitshed.dev" rel="noopener">bitshed.dev</a>
            <a href="https://dor.ky" rel="me noopener">dor.ky</a>
            <a href="<?= $e($config->repositoryUrl) ?>" rel="noopener">GitHub</a>
        </div>
        <span>Built <?= $e($generatedAt->format('j M Y, H:i')) ?> UTC</span>
    </footer>
</div>
<script>
document.querySelectorAll('[data-tt]').forEach(function (b) {
    b.addEventListener('click', function () {
        var d = document.documentElement;
        var light = d.dataset.theme ? d.dataset.theme === 'light' : matchMedia('(prefers-color-scheme: light)').matches;
        d.dataset.theme = light ? 'dark' : 'light';
        try { localStorage.setItem('theme', d.dataset.theme) } catch (e) {}
    });
});
document.querySelectorAll('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
        var label = b.textContent;
        navigator.clipboard.writeText(b.dataset.copy).then(function () {
            b.textContent = 'Copied';
        }, function () {
            b.textContent = 'Copy failed';
        }).then(function () {
            setTimeout(function () { b.textContent = label; }, 1600);
        });
    });
});
</script>
</body>
</html>
