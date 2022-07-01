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

use Phar;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

use function file_get_contents;
use function file_put_contents;
use function tempnam;

class PlantumlRenderer implements DiagramRenderer
{
    private LoggerInterface $logger;
    private string $plantUmlBinaryPath;

    public function __construct(LoggerInterface $logger, string $plantUmlBinaryPath)
    {
        $this->logger = $logger;
        $this->plantUmlBinaryPath = $plantUmlBinaryPath;
    }

    public function render(string $diagram): ?string
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

        $pumlFileLocation = tempnam('phpdocumentor', 'pu_');
        file_put_contents($pumlFileLocation, $output);

        $process = new Process([$this->plantUmlBinaryPath, '-tsvg', $pumlFileLocation], __DIR__, null, null, 600.0);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->error('Generating the class diagram failed', ['error' => $process->getErrorOutput()]);

            return null;
        }

        return file_get_contents($pumlFileLocation . '.svg') ?: null;
    }
}
