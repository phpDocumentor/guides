<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ThemeBootstrap\Command;

use Flyfinder\Finder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Tactician\CommandBus;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Metas;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function array_pop;
use function closedir;
use function copy;
use function count;
use function getcwd;
use function implode;
use function is_dir;
use function mkdir;
use function opendir;
use function readdir;
use function sprintf;
use function str_starts_with;
use function strtoupper;

final class RunBootstrap extends Command
{
    public function __construct(private readonly CommandBus $commandBus, private readonly Metas $metas)
    {
        parent::__construct('run');

        $this->addArgument(
            'input',
            InputArgument::OPTIONAL,
            'Directory to read for files',
            'docs',
        );
        $this->addArgument(
            'output',
            InputArgument::OPTIONAL,
            'Directory to read for files',
            'output',
        );

        $this->addOption(
            'input-format',
            null,
            InputOption::VALUE_OPTIONAL,
            'Format of the input can be RST, or Markdown',
            'rst',
        );
        $this->addOption(
            'output-format',
            null,
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Format of the input can be html',
            ['html'],
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->metas->reset();
        $inputDir = $this->getAbsolutePath((string) ($input->getArgument('input') ?? ''));
        if (!is_dir($inputDir)) {
            throw new RuntimeException(sprintf('Input directory "%s" was not found! ' . "\n" .
                'Run "vendor/bin/guides -h" for information on how to configure this command.', $inputDir));
        }

        $outputDir = $this->getAbsolutePath((string) ($input->getArgument('output') ?? ''));
        $sourceFileSystem = new Filesystem(new Local($input->getArgument('input')));
        $sourceFileSystem->addPlugin(new Finder());
        $documents = $this->commandBus->handle(
            new ParseDirectoryCommand(
                $sourceFileSystem,
                '',
                $input->getOption('input-format'),
            ),
        );

        $documents = $this->commandBus->handle(new CompileDocumentsCommand($documents));

        $destinationFileSystem = new Filesystem(new Local($outputDir));
        $this->copyAssets($outputDir);
        $outputFormats = $input->getOption('output-format');

        foreach ($outputFormats as $format) {
            $this->commandBus->handle(
                new RenderCommand(
                    $format,
                    $documents,
                    $this->metas,
                    $sourceFileSystem,
                    $destinationFileSystem,
                ),
            );
        }

        $lastFormat = '';

        if (count($outputFormats) > 1) {
            $lastFormat = (count($outputFormats) > 2 ? ',' : '') . ' and ' . strtoupper(array_pop($outputFormats));
        }

        $formatsText = strtoupper(implode(', ', $outputFormats)) . $lastFormat;

        $output->writeln(
            'Successfully placed ' . count($documents) . ' rendered ' . $formatsText . ' files into ' . $outputDir,
        );

        return 0;
    }

    private function copyAssets(string $outputDir): void
    {
        $this->recurseCopy(getcwd() . '/vendor/phpdocumentor/guides-theme-bootstrap/resources/assets', $outputDir . '/assets/');
    }

    private function recurseCopy(string $src, string $dst): void
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (( $file = readdir($dir)) !== false) {
            if (( $file === '.' ) || ( $file === '..' )) {
                continue;
            }

            if (is_dir($src . '/' . $file)) {
                $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }

        closedir($dir);
    }

    private function getAbsolutePath(string $path): string
    {
        if (!str_starts_with($path, '/')) {
            if (getcwd() === false) {
                throw new RuntimeException('Cannot find current working directory, use absolute paths.');
            }

            $path = getcwd() . '/' . $path;
        }

        return $path;
    }
}
