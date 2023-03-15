<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Integration;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Compiler\NodeTransformers\TocNodeTransformer;
use phpDocumentor\Guides\Compiler\Passes\MetasPass;
use phpDocumentor\Guides\Compiler\Passes\TransformerPass;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryHandler;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\ParseFileHandler;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentHandler;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\RenderContext;
use League\Tactician\Setup\QuickStart;
use phpDocumentor\Guides\Twig\TwigRenderer;
use phpDocumentor\Guides\UrlGenerator;
use PHPUnit\Framework\TestCase;
use Flyfinder\Finder;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\AbstractLogger;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use function setlocale;

use const LC_ALL;

/**
 * Integration tests for the bootstrap template found in phpdocumentor/guides-theme-bootstrap
 */
class IntegrationBootstrapTest extends TestCase
{
    private const TEST_DIR = 'tests-bootstrap';

    protected function setUp(): void
    {
        setlocale(LC_ALL, 'en_US.utf8');
    }

    /**
     * @param String[] $compareFiles
     * @dataProvider getIntegrationTests
     */
    public function testIntegration(
        string $inputPath,
        string $expectedPath,
        string $outputPath,
        array $compareFiles
    ): void {
        system("rm -rf " . escapeshellarg($outputPath));
        system("mkdir " . escapeshellarg($outputPath));

        $metas = new Metas([]);
        $logger = new class($outputPath) extends AbstractLogger {

            private $outputPath;

            public function __construct($outputPath)
            {
                $this->outputPath = $outputPath;
            }

            public function log($level, $message, array $context = []): void
            {
                $message = $level . ':' . $message . PHP_EOL;
                file_put_contents($this->outputPath . '/log' . $level . '.txt', $message, FILE_APPEND);
            }
        };

        $commandbus = QuickStart::create(
            [
                ParseFileCommand::class => new ParseFileHandler(
                    $logger,
                    new class implements EventDispatcherInterface {
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
            $inputPath
        ));
        $sourceFileSystem->addPlugin(new Finder());

        $parseDirCommand = new ParseDirectoryCommand(
            $sourceFileSystem,
            '',
            'rst'
        );

        $documents = $parseDirectoryHandler->handle($parseDirCommand);
        $compliler = new Compiler([
            new MetasPass($metas),
            new TransformerPass(
                new DocumentNodeTraverser(
                    [
                        new TocNodeTransformer($metas)
                    ]
                )
            )
        ]);
        $documents = $compliler->run($documents);

        /** @var TwigRenderer $renderer */
        $renderer = \phpDocumentor\Guides\Setup\QuickStart::createRenderer (
            $metas,

            [
                __DIR__  . '/../../packages/guides-theme-bootstrap/resources/template'
            ],
        );
        $renderDocumentHandler = new RenderDocumentHandler($renderer);

        foreach ($documents as $document) {
            $renderDocumentHandler->handle(
                new RenderDocumentCommand(
                    $document,
                    RenderContext::forDocument(
                        $document,
                        $sourceFileSystem,
                        new Filesystem(new Local($outputPath)),
                        '/',
                        $metas,
                        new UrlGenerator(),
                        'html'
                    )
                )
            );
        }

        foreach ($compareFiles as $compareFile) {
            $outputFile = str_replace($expectedPath, $outputPath, $compareFile);
            self::assertFileExists($outputFile);
            self::assertFileEquals($compareFile, $outputFile);
        }
    }

    /**
     * @return mixed[]
     */
    public function getIntegrationTests(): array
    {
        $finder = new SymfonyFinder();
        $finder
            ->directories()
            ->in(__DIR__ . '/' . self::TEST_DIR);

        $tests = [];

        foreach ($finder as $dir) {

            if (file_exists($dir->getPathname() . '/input')) {
                $compareFiles = [];
                $fileFinder = new \Symfony\Component\Finder\Finder();
                $fileFinder
                    ->files()
                    ->in($dir->getPathname() . '/expected');
                foreach ($fileFinder as $file) {
                    $compareFiles[] = $file->getPathname();
                }
                $tests[] = [
                    $dir->getPathname() . '/input',
                    $dir->getPathname() . '/expected',
                    $dir->getPathname() . '/temp',
                    $compareFiles
                ];
            }
        }

        return $tests;
    }
}
