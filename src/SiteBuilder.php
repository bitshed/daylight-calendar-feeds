<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use DateTimeImmutable;
use RuntimeException;

/**
 * Writes the static site: one .ics feed per location plus the index page.
 */
final readonly class SiteBuilder
{
    public function __construct(
        private Config $config,
        private FeedBuilder $feeds,
        private string $templateDirectory,
        private string $assetDirectory,
    ) {
    }

    /**
     * @return list<string> Paths written, relative to the output directory.
     */
    public function build(string $outputDirectory, DateTimeImmutable $now): array
    {
        $written = [];
        $listings = [];

        foreach ($this->config->locations as $location) {
            $reports = $this->feeds->reports($location, $now);
            $path = Config::feedPath($location);
            $this->write($outputDirectory, $path, $this->feeds->ics($location, $reports, $now));
            $written[] = $path;

            $url = $this->config->feedUrl($location);
            $listings[] = [
                'location' => $location,
                'url' => $url,
                'webcal' => (string) preg_replace('#^https?://#', 'webcal://', $url),
                'path' => $path,
                // Reports start past_days before today, so today sits at that index.
                'today' => $reports[$this->config->pastDays],
            ];
        }

        $this->write($outputDirectory, 'index.html', Template::render(
            $this->templateDirectory . '/index.php',
            [
                'config' => $this->config,
                'feeds' => $listings,
                'generatedAt' => $now,
            ],
        ));
        $written[] = 'index.html';

        foreach ($this->assets() as $asset) {
            $this->write($outputDirectory, $asset, $this->read($this->assetDirectory . '/' . $asset));
            $written[] = $asset;
        }

        // GitHub Pages: serve files as-is, and on the configured custom domain.
        $this->write($outputDirectory, '.nojekyll', '');
        $this->write($outputDirectory, 'CNAME', (string) parse_url($this->config->baseUrl, PHP_URL_HOST) . "\n");
        array_push($written, '.nojekyll', 'CNAME');

        return $written;
    }

    /**
     * @return list<string>
     */
    private function assets(): array
    {
        $files = scandir($this->assetDirectory);

        if ($files === false) {
            throw new RuntimeException(sprintf('Cannot read asset directory %s.', $this->assetDirectory));
        }

        return array_values(array_filter(
            $files,
            fn (string $file): bool => is_file($this->assetDirectory . '/' . $file) && !str_starts_with($file, '.'),
        ));
    }

    private function read(string $path): string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(sprintf('Cannot read %s.', $path));
        }

        return $contents;
    }

    private function write(string $outputDirectory, string $relativePath, string $contents): void
    {
        $path = $outputDirectory . '/' . $relativePath;
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0o755, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Cannot create directory %s.', $directory));
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException(sprintf('Cannot write %s.', $path));
        }
    }
}
