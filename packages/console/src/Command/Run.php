<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Console\Command;

use Flyfinder\Finder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Tactician\CommandBus;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Metas;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class Run extends Command
{
    private CommandBus $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        parent::__construct('run');

        $this->addArgument(
            'input',
            InputArgument::OPTIONAL,
            'Directory to read for files',
            'docs'
        );
        $this->addArgument(
            'output',
            InputArgument::OPTIONAL,
            'Directory to read for files',
            'output'
        );

        $this->addOption(
            'input-format',
            null,
            InputOption::VALUE_OPTIONAL,
            'Format of the input can be RST, or Markdown',
            'rst'
        );
        $this->addOption(
            'output-format',
            null,
            InputOption::VALUE_OPTIONAL,
            'Format of the input can be html',
            'html'
        );

        $this->commandBus = $commandBus;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $metas = new Metas([]);
        $sourceFileSystem = new Filesystem(new Local($input->getArgument('input')));
        $sourceFileSystem->addPlugin(new Finder());

        $documents = $this->commandBus->handle(
            new ParseDirectoryCommand(
                $sourceFileSystem,
                '',
                $input->getOption('input-format')
            )
        );

        $documents = $this->commandBus->handle(new CompileDocumentsCommand($documents));

        $destinationFileSystem = new Filesystem(new Local($input->getArgument('output')));

        $this->commandBus->handle(
            new RenderCommand(
                $input->getOption('output-format'),
                $documents,
                $metas,
                $sourceFileSystem,
                $destinationFileSystem
            )
        );

        return 0;
    }
}
