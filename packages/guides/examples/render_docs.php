<?php

declare(strict_types=1);
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Compiler\Passes\MetasPass;
use phpDocumentor\Guides\Compiler\Passes\TransformerPass;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Compiler\NodeTransformers\TocNodeTransformer;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\CompileDocumentsHandler;
use phpDocumentor\Guides\Handlers\RenderDocumentHandler;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Nodes\DocumentNode;
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

require __DIR__ . '/../../../vendor/autoload.php';
//require __DIR__ . '/../../guides-restructured-text/vendor/autoload.php';

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
            $logger,
            new class implements EventDispatcherInterface
            {
                public function dispatch(object $event)
                {
                    return $event;
                }
            },
            \phpDocumentor\Guides\Setup\QuickStart::createRstParser()
        ),

        CompileDocumentsCommand::class => new CompileDocumentsHandler(
            new Compiler([
                new MetasPass($metas),
                new TransformerPass(
                    new DocumentNodeTraverser(
                        [
                            new TocNodeTransformer($metas)
                        ]
                    )
                )
            ])
        ),

        RenderDocumentCommand::class => new RenderDocumentHandler(
            \phpDocumentor\Guides\Setup\QuickStart::createRenderer($metas)
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
    '',
    'rst'
);

$documents = $parseDirectoryHandler->handle($parseDirCommand);

$compileDocumentsCommand = new CompileDocumentsCommand($documents);

/** @var DocumentNode[] $documents */
$documents = $commandbus->handle($compileDocumentsCommand);

foreach ($documents as $document) {
    echo "Render: " . $document->getFilePath() . PHP_EOL;

    try {
        $commandbus->handle(
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
