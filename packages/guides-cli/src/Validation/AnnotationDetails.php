<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Cli\Validation;

use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Validation\Violation;
use Stringable;

use function getcwd;
use function is_int;
use function is_scalar;
use function is_string;
use function ltrim;
use function preg_match;
use function preg_replace_callback;
use function rtrim;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

/**
 * Where a violation is found and what it says, as code review tools show it on the lines of a change.
 */
final class AnnotationDetails
{
    public function __construct(private readonly SettingsManager $settingsManager)
    {
    }

    /**
     * The path of the source file from the root of the repository, which is the working directory of a CI job.
     */
    public function getFile(Violation $violation): string|null
    {
        $document = $violation->context['rst-file'] ?? null;
        if (!is_string($document) || $document === '') {
            return null;
        }

        $settings = $this->settingsManager->getProjectSettings();
        // Depending on where a violation is found, the document is named with or without its extension
        if (preg_match('/\.(rst|md)$/', $document) !== 1) {
            $document .= '.' . $settings->getInputFormat();
        }

        return self::fromWorkingDirectory(rtrim($settings->getInput(), '/') . '/' . ltrim($document, '/'));
    }

    /**
     * The input directory, as the closest place for violations that are not found in a particular file.
     */
    public function getInputDirectory(): string
    {
        return self::fromWorkingDirectory(rtrim($this->settingsManager->getProjectSettings()->getInput(), '/'));
    }

    public function getLine(Violation $violation): int|null
    {
        $line = $violation->context['currentLineNumber'] ?? null;

        return is_int($line) && $line > 0 ? $line : null;
    }

    /**
     * The message with placeholders like "{file}" replaced by the value from the context, as a PSR-3 logger would.
     */
    public function getMessage(Violation $violation): string
    {
        return trim((string) preg_replace_callback(
            '/{([\w.]+)}/',
            static function (array $matches) use ($violation): string {
                $value = $violation->context[$matches[1]] ?? null;

                return is_scalar($value) || $value instanceof Stringable ? (string) $value : $matches[0];
            },
            $violation->message,
        ));
    }

    private static function fromWorkingDirectory(string $path): string
    {
        $workingDirectory = getcwd();
        if ($workingDirectory !== false && str_starts_with($path, rtrim($workingDirectory, '/') . '/')) {
            $path = substr($path, strlen(rtrim($workingDirectory, '/')) + 1);
        }

        return str_starts_with($path, './') ? substr($path, 2) : $path;
    }
}
