<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use DateTimeImmutable;

final readonly class CalendarEvent
{
    public function __construct(
        public string $uid,
        public DateTimeImmutable $date,
        public string $summary,
        public string $description,
    ) {
    }
}
