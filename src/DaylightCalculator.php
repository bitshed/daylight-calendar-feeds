<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use DateTimeImmutable;

/**
 * Works out sunrise, sunset and daylight for a location, and how much daylight
 * has been gained since the most recent winter solstice there.
 *
 * The solstice is taken to be the shortest day found in a window around the
 * astronomical date (late December in the north, late June in the south). That
 * keeps "gained" consistent with the daylight figures themselves, to the second,
 * rather than mixing two separate astronomical models.
 */
final class DaylightCalculator
{
    private const SECONDS_PER_DAY = 86400;

    /** Days of the month searched for the shortest day. */
    private const SOLSTICE_WINDOW = [17, 25];

    /** @var array<string, array{DateTimeImmutable, int}> */
    private array $shortestDays = [];

    public function report(Location $location, DateTimeImmutable $day): DayReport
    {
        $day = $this->localMidnight($location, $day);
        [$sunrise, $sunset, $daylight] = $this->sunTimes($location, $day);
        [$solstice, $shortest] = $this->mostRecentSolstice($location, $day);

        return new DayReport(
            location: $location,
            date: $day,
            sunrise: $sunrise,
            sunset: $sunset,
            daylightSeconds: $daylight,
            gainedSeconds: $daylight - $shortest,
            solstice: $solstice,
        );
    }

    /**
     * @return array{?DateTimeImmutable, ?DateTimeImmutable, int}
     */
    private function sunTimes(Location $location, DateTimeImmutable $day): array
    {
        // Asking at local noon pins the calculation to the right calendar day
        // whatever the UTC offset.
        $info = date_sun_info($day->setTime(12, 0)->getTimestamp(), $location->latitude, $location->longitude);
        $sunrise = $info['sunrise'];
        $sunset = $info['sunset'];

        if (!is_int($sunrise) || !is_int($sunset)) {
            // true: the sun stays up all day. false: it never rises.
            return [null, null, $sunrise === true ? self::SECONDS_PER_DAY : 0];
        }

        return [
            $this->localTime($location, $sunrise),
            $this->localTime($location, $sunset),
            $sunset - $sunrise,
        ];
    }

    /**
     * @return array{DateTimeImmutable, int}
     */
    private function mostRecentSolstice(Location $location, DateTimeImmutable $day): array
    {
        $year = (int) $day->format('Y');
        $solstice = $this->shortestDay($location, $year);

        if ($solstice[0] > $day) {
            $solstice = $this->shortestDay($location, $year - 1);
        }

        return $solstice;
    }

    /**
     * @return array{DateTimeImmutable, int}
     */
    private function shortestDay(Location $location, int $year): array
    {
        $key = $location->slug . ':' . $year;

        if (isset($this->shortestDays[$key])) {
            return $this->shortestDays[$key];
        }

        $month = $location->isSouthernHemisphere() ? 6 : 12;
        [$first, $last] = self::SOLSTICE_WINDOW;
        $shortest = null;

        for ($dayOfMonth = $first; $dayOfMonth <= $last; $dayOfMonth++) {
            $day = (new DateTimeImmutable('now', $location->timezone))
                ->setDate($year, $month, $dayOfMonth)
                ->setTime(0, 0);
            $daylight = $this->sunTimes($location, $day)[2];

            if ($shortest === null || $daylight < $shortest[1]) {
                $shortest = [$day, $daylight];
            }
        }

        assert($shortest !== null);

        return $this->shortestDays[$key] = $shortest;
    }

    private function localMidnight(Location $location, DateTimeImmutable $day): DateTimeImmutable
    {
        return (new DateTimeImmutable($day->format('Y-m-d'), $location->timezone))->setTime(0, 0);
    }

    private function localTime(Location $location, int $timestamp): DateTimeImmutable
    {
        return (new DateTimeImmutable('@' . $timestamp))->setTimezone($location->timezone);
    }
}
