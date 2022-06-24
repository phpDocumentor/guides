<?php

declare(strict_types=1);
use phpDocumentor\Guides\Handlers\RenderDocumentHandler;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\RenderContext;

use Flyfinder\Finder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Tactician\Setup\QuickStart;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryHandler;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\ParseFileHandler;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\UrlGenerator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\AbstractLogger;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../../guides-restructured-text/vendor/autoload.php';

$metas = new Metas([]);
$logger = new class extends AbstractLogger {
    public function log($level, $message, array $context = []): void
    {
        echo $level . ':' . $message . PHP_EOL;
    }
};

$commandbus = QuickStart::create(
    [
        ParseFileCommand::class => new ParseFileHandler(
            $metas,
            $logger,
            new class implements EventDispatcherInterface
            {
                public function dispatch(object $event)
                {
                    return $event;
                }
            },
            \phpDocumentor\Guides\Setup\QuickStart::createRstParser()
        )
    ]
);

$parseDirectoryHandler = new ParseDirectoryHandler(
    new FileCollector($metas),
    $commandbus,
);

$sourceFileSystem = new Filesystem(new Local(
    __DIR__  . '/../docs'
));
$sourceFileSystem->addPlugin(new Finder());

$parseDirCommand = new ParseDirectoryCommand(
    $sourceFileSystem,
    './',
    'rst'
);

$documents = $parseDirectoryHandler->handle($parseDirCommand);

$renderer = \phpDocumentor\Guides\Setup\QuickStart::createRenderer();
$renderDocumentHandler = new RenderDocumentHandler($renderer);

foreach ($documents as $document) {
    echo "Render: " . $document->getFilePath() . PHP_EOL;

    try {
        $renderDocumentHandler->handle(
            new RenderDocumentCommand(
                $document,
                RenderContext::forDocument(
                    $document,
                    $sourceFileSystem,
                    new Filesystem(new Local(__DIR__ . '/out')),
                    '/example/',
                    $metas,
                    new UrlGenerator(),
                    'html'
                )
            )
        );
    } catch (Exception $e) {
        echo "Error:" . $e->getMessage() . PHP_EOL;
    }
}
