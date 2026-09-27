<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use DateTimeImmutable;

/**
 * Sun times for one location on one local calendar day.
 *
 * Sunrise and sunset are null on days when the sun never crosses the horizon:
 * polar day (daylight is the full 24 hours) or polar night (daylight is zero).
 */
final readonly class DayReport
{
    public function __construct(
        public Location $location,
        public DateTimeImmutable $date,
        public ?DateTimeImmutable $sunrise,
        public ?DateTimeImmutable $sunset,
        public int $daylightSeconds,
        public int $gainedSeconds,
        public DateTimeImmutable $solstice,
    ) {
    }

    /**
     * The timezone abbreviation in force on this day, such as GMT or BST.
     */
    public function timezoneAbbreviation(): string
    {
        return $this->date->setTime(12, 0)->format('T');
    }
}
