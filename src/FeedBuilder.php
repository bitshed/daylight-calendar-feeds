<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use DateTimeImmutable;

/**
 * Produces the day reports and the .ics document for one location.
 */
final readonly class FeedBuilder
{
    public function __construct(
        private Config $config,
        private DaylightCalculator $calculator,
    ) {
    }

    /**
     * The location's local date at the given moment.
     */
    public function today(Location $location, DateTimeImmutable $now): DateTimeImmutable
    {
        return $now->setTimezone($location->timezone)->setTime(0, 0);
    }

    /**
     * @return list<DayReport>
     */
    public function reports(Location $location, DateTimeImmutable $now): array
    {
        $today = $this->today($location, $now);
        $reports = [];

        for ($offset = -$this->config->pastDays; $offset <= $this->config->futureDays; $offset++) {
            $reports[] = $this->calculator->report($location, $today->modify(sprintf('%+d days', $offset)));
        }

        return $reports;
    }

    /**
     * @param list<DayReport> $reports
     */
    public function ics(Location $location, array $reports, DateTimeImmutable $now): string
    {
        $host = (string) parse_url($this->config->baseUrl, PHP_URL_HOST);

        $calendar = new IcsCalendar(
            name: 'Daylight: ' . $location->name,
            description: sprintf(
                'Sunrise, sunset and daylight gained since the winter solstice for %s, %s.',
                $location->name,
                $location->region,
            ),
            generatedAt: $now,
        );

        return $calendar->render(array_map(
            static fn (DayReport $report): CalendarEvent => new CalendarEvent(
                uid: sprintf('%s-%s@%s', $report->date->format('Ymd'), $location->slug, $host),
                date: $report->date,
                summary: EventText::title($report),
                description: EventText::description($report),
            ),
            $reports,
        ));
    }
}
