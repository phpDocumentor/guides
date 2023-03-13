<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Console\Command;

use Flyfinder\Finder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Tactician\CommandBus;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Parser;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UrlGeneratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class Run extends Command
{
    private CommandBus $commandBus;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(CommandBus $commandBus, UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct('run');

        $this->addArgument('input', InputArgument::OPTIONAL, 'Directory to read for files', 'docs');
        $this->addArgument('output', InputArgument::OPTIONAL, 'Directory to read for files', 'output');

        $this->addOption('input-format', null, InputOption::VALUE_OPTIONAL, 'Format of the input can be RST, or Markdown', 'rst');
        $this->addOption('output-format', null, InputOption::VALUE_OPTIONAL, 'Format of the input can be html', 'html');

        $this->commandBus = $commandBus;
        $this->urlGenerator = $urlGenerator;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $metas = new Metas([]);
        $sourceFileSystem = new Filesystem(new Local($input->getArgument('input')));
        $sourceFileSystem->addPlugin(new Finder());

        $documents = $this->commandBus->handle(
            new ParseDirectoryCommand(
                $sourceFileSystem,
                './',
                $input->getOption('input-format')
            )
        );

        $destinationFileSystem = new Filesystem(new Local($input->getArgument('output')));

        foreach ($documents as $document) {
            $this->commandBus->handle(
                new RenderDocumentCommand(
                    $document,
                    RenderContext::forDocument(
                        $document,
                        $sourceFileSystem,
                        $destinationFileSystem,
                        './',
                        $metas,
                        $this->urlGenerator,
                        $input->getOption('output-format')
                    )
                )
            );
        }

        return 0;
    }
}
