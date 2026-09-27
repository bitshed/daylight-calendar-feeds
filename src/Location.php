<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use DateTimeZone;
use InvalidArgumentException;

final readonly class Location
{
    private const SLUG_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        public string $slug,
        public string $name,
        public string $region,
        public float $latitude,
        public float $longitude,
        public DateTimeZone $timezone,
    ) {
        if (preg_match(self::SLUG_PATTERN, $slug) !== 1) {
            throw new InvalidArgumentException(sprintf(
                'Location slug "%s" must be lowercase letters, digits and single hyphens.',
                $slug,
            ));
        }

        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new InvalidArgumentException(sprintf('Latitude for "%s" must be between -90 and 90.', $slug));
        }

        if ($longitude < -180.0 || $longitude > 180.0) {
            throw new InvalidArgumentException(sprintf('Longitude for "%s" must be between -180 and 180.', $slug));
        }
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $timezone = self::string($data, 'timezone');

        if (!in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            throw new InvalidArgumentException(sprintf('Unknown timezone "%s".', $timezone));
        }

        return new self(
            slug: self::string($data, 'slug'),
            name: self::string($data, 'name'),
            region: self::string($data, 'region'),
            latitude: self::float($data, 'latitude'),
            longitude: self::float($data, 'longitude'),
            timezone: new DateTimeZone($timezone),
        );
    }

    public function isSouthernHemisphere(): bool
    {
        return $this->latitude < 0.0;
    }

    /**
     * @param array<mixed> $data
     */
    private static function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (!is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException(sprintf('Location field "%s" must be a non-empty string.', $key));
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     */
    private static function float(array $data, string $key): float
    {
        $value = $data[$key] ?? null;

        if (!is_int($value) && !is_float($value)) {
            throw new InvalidArgumentException(sprintf('Location field "%s" must be a number.', $key));
        }

        return (float) $value;
    }
}
