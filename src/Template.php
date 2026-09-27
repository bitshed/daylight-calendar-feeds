<?php

declare(strict_types=1);

namespace Bitshed\Daylight;

use RuntimeException;
use Throwable;

/**
 * Renders a plain PHP template. Templates receive their data as variables,
 * plus $e, a closure that HTML-escapes a value.
 */
final class Template
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $file, array $data): string
    {
        if (!is_file($file)) {
            throw new RuntimeException(sprintf('Template %s not found.', $file));
        }

        $data['e'] = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        ob_start();

        try {
            (static function (string $__file, array $__data): void {
                extract($__data, EXTR_SKIP);
                require $__file;
            })($file, $data);
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        return (string) ob_get_clean();
    }
}
