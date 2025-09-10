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

namespace phpDocumentor\Guides\Cli\Internal;

use Flyfinder\Path;
use Flyfinder\Specification\InPath;
use Flyfinder\Specification\NotSpecification;
use Flyfinder\Specification\OrSpecification;
use Flyfinder\Specification\SpecificationInterface;
use League\Tactician\CommandBus;
use phpDocumentor\FileSystem\Finder\Exclude;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use Symfony\Component\Console\Input\InputInterface;

use function array_map;
use function array_reduce;
use function array_shift;
use function assert;
use function method_exists;

class RunCommandHandler
{
    public function __construct(
        private CommandBus $commandBus,
        private ThemeManager $themeManager,
    ) {
    }

    /** @return DocumentNode[] */
    public function handle(RunCommand $command): array
    {
        $settings = $command->settings;
        $projectNode = $command->projectNode;
        $outputDir = $settings->getOutput();
        $sourceFileSystem = FlySystemAdapter::createForPath($settings->getInput());
        $documents = [];
        if ($settings->getInputFile() === '') {
            $documents = $this->commandBus->handle(
                new ParseDirectoryCommand(
                    $sourceFileSystem,
                    '',
                    $settings->getInputFormat(),
                    $projectNode,
                    $this->getExclude($settings, $command->input),
                ),
            );
        } else {
            $documents[] = $this->commandBus->handle(
                new ParseFileCommand(
                    $sourceFileSystem,
                    '',
                    $settings->getInputFile(),
                    $settings->getInputFormat(),
                    1,
                    $projectNode,
                    true,
                ),
            );
        }

        $this->themeManager->useTheme($settings->getTheme());

        $documents = $this->commandBus->handle(new CompileDocumentsCommand($documents, new CompilerContext($projectNode)));

        $destinationFileSystem = FlySystemAdapter::createForPath($outputDir);

        $outputFormats = $settings->getOutputFormats();

        foreach ($outputFormats as $format) {
            $this->commandBus->handle(
                new RenderCommand(
                    $format,
                    $documents,
                    $sourceFileSystem,
                    $destinationFileSystem,
                    $projectNode,
                ),
            );
        }

        return $documents;
    }

    private function getExclude(ProjectSettings $settings, InputInterface|null $input = null): Exclude|SpecificationInterface|null
    {
        if (method_exists($settings, 'getExcludes')) {
            return $settings->getExcludes();
        }

        if ($input === null) {
            return null;
        }

        if ($input->getOption('exclude-path')) {
            /** @var string[] $excludedPaths */
            $excludedPaths = (array) $input->getOption('exclude-path');
            $excludedSpecifications = array_map(static fn (string $path) => new NotSpecification(new InPath(new Path($path))), $excludedPaths);
            $excludedSpecification = array_shift($excludedSpecifications);
            assert($excludedSpecification !== null);

            return array_reduce(
                $excludedSpecifications,
                static fn (SpecificationInterface $carry, SpecificationInterface $spec) => new OrSpecification($carry, $spec),
                $excludedSpecification,
            );
        }

        return null;
    }
}
