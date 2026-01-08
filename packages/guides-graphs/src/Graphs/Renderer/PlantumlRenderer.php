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

namespace phpDocumentor\Guides\Graphs\Renderer;

use phpDocumentor\Guides\RenderContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

use function array_merge;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

final class PlantumlRenderer implements DiagramRenderer
{
    private readonly string $tempDirectory;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $plantUmlBinaryPath,
        string|null $tempDirectory = null,
    ) {
        $this->tempDirectory = $tempDirectory ?? sys_get_temp_dir() . '/phpdocumentor';
    }

    public function render(RenderContext $renderContext, string $diagram): string|null
    {
        $output = <<<PUML
@startuml
   skinparam ArrowColor #516f42
   skinparam activityBorderColor #516f42
   skinparam activityBackgroundColor #ffffff
   skinparam activityDiamondBorderColor #516f42
   skinparam activityDiamondBackgroundColor #ffffff
   skinparam shadowing false

$diagram
@enduml
PUML;

        if (!$this->ensureDirectoryExists($this->tempDirectory)) {
            $this->logger->error(
                'Failed to create temp directory: ' . $this->tempDirectory,
                $renderContext->getLoggerInformation(),
            );

            return null;
        }

        $pumlFileLocation = tempnam($this->tempDirectory, 'pu_');
        if ($pumlFileLocation === false) {
            $this->logger->error(
                'Failed to create temporary file for diagram',
                $renderContext->getLoggerInformation(),
            );

            return null;
        }

        file_put_contents($pumlFileLocation, $output);
        try {
            $process = new Process([$this->plantUmlBinaryPath, '-tsvg', $pumlFileLocation], __DIR__, null, null, 600.0);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->logger->error(
                    'Generating the class diagram failed',
                    array_merge(
                        ['error' => $process->getErrorOutput()],
                        $renderContext->getLoggerInformation(),
                    ),
                );

                return null;
            }
        } catch (RuntimeException $e) {
            $this->logger->error(
                'Generating the class diagram failed',
                array_merge(
                    ['error' => $e->getMessage()],
                    $renderContext->getLoggerInformation(),
                ),
            );

            return null;
        }

        $svg = file_get_contents($pumlFileLocation . '.svg') ?: null;

        @unlink($pumlFileLocation);
        @unlink($pumlFileLocation . '.svg');

        return $svg;
    }

    /**
     * Ensures the directory exists, handling race conditions safely.
     *
     * @return bool True if directory exists or was created, false on failure
     */
    private function ensureDirectoryExists(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        // Attempt to create the directory (suppress warning if concurrent process creates it)
        if (@mkdir($directory, 0o755, true)) {
            return true;
        }

        // mkdir failed - check if another process created it (race condition)
        return is_dir($directory);
    }
}
