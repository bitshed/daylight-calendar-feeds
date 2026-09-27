<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use InvalidArgumentException;

final readonly class Config
{
    /**
     * @param list<Location> $locations
     */
    public function __construct(
        public string $title,
        public string $baseUrl,
        public string $repositoryUrl,
        public string $issuesUrl,
        public int $pastDays,
        public int $futureDays,
        public array $locations,
    ) {
        if ($pastDays < 0 || $futureDays < 0) {
            throw new InvalidArgumentException('Feed past_days and future_days cannot be negative.');
        }

        $slugs = array_map(static fn (Location $location): string => $location->slug, $locations);
        $duplicates = array_keys(array_filter(array_count_values($slugs), static fn (int $count): bool => $count > 1));

        if ($duplicates !== []) {
            throw new InvalidArgumentException('Duplicate location slugs: ' . implode(', ', $duplicates) . '.');
        }
    }

    public static function load(string $path): self
    {
        $data = require $path;

        if (!is_array($data)) {
            throw new InvalidArgumentException(sprintf('%s must return an array.', $path));
        }

        return self::fromArray($data);
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $site = self::section($data, 'site');
        $feed = self::section($data, 'feed');

        return new self(
            title: self::string($site, 'title'),
            baseUrl: rtrim(self::string($site, 'base_url'), '/'),
            repositoryUrl: self::string($site, 'repository_url'),
            issuesUrl: self::string($site, 'issues_url'),
            pastDays: self::int($feed, 'past_days'),
            futureDays: self::int($feed, 'future_days'),
            locations: array_map(
                static fn (mixed $location): Location => Location::fromArray(
                    is_array($location) ? $location : throw new InvalidArgumentException('Each location must be an array.'),
                ),
                array_values(self::section($data, 'locations')),
            ),
        );
    }

    public function feedUrl(Location $location): string
    {
        return $this->baseUrl . '/' . self::feedPath($location);
    }

    public static function feedPath(Location $location): string
    {
        return 'feeds/' . $location->slug . '.ics';
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private static function section(array $data, string $key): array
    {
        return is_array($data[$key] ?? null)
            ? $data[$key]
            : throw new InvalidArgumentException(sprintf('Config section "%s" must be an array.', $key));
    }

    /**
     * @param array<mixed> $data
     */
    private static function string(array $data, string $key): string
    {
        return is_string($data[$key] ?? null) && $data[$key] !== ''
            ? $data[$key]
            : throw new InvalidArgumentException(sprintf('Config value "%s" must be a non-empty string.', $key));
    }

    /**
     * @param array<mixed> $data
     */
    private static function int(array $data, string $key): int
    {
        return is_int($data[$key] ?? null)
            ? $data[$key]
            : throw new InvalidArgumentException(sprintf('Config value "%s" must be an integer.', $key));
    }
}
