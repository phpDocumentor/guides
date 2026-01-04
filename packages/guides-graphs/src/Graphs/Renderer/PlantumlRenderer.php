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

        if (!is_dir($this->tempDirectory)) {
            mkdir($this->tempDirectory, 0o755, true);
        }

        $pumlFileLocation = tempnam($this->tempDirectory, 'pu_');
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

        return file_get_contents($pumlFileLocation . '.svg') ?: null;
    }
}
