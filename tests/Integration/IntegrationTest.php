<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Integration;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Compiler\NodeTransformers\DefaultNodeTransformerFactory;
use phpDocumentor\Guides\Compiler\Passes\MetasPass;
use phpDocumentor\Guides\Compiler\Passes\TransformerPass;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\CompileDocumentsHandler;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryHandler;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\ParseFileHandler;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Handlers\RenderHandler;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\DocumentNode;
use League\Tactician\Setup\QuickStart;
use phpDocumentor\Guides\Renderer\DefaultTypeRendererFactory;
use phpDocumentor\Guides\Renderer\HtmlTypeRenderer;
use phpDocumentor\Guides\Renderer\IntersphinxTypeRenderer;
use phpDocumentor\Guides\Renderer\TypeRenderer;
use PHPUnit\Framework\TestCase;
use Flyfinder\Finder;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\AbstractLogger;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use function setlocale;

use const LC_ALL;

class IntegrationTest extends TestCase
{
    private const RENDER_DOCUMENT_FILES = ['main-directive'];
    private const SKIP_INDENTER_FILES = ['code-block-diff'];

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
                ),
                CompileDocumentsCommand::class => new CompileDocumentsHandler(
                    new Compiler([
                        new MetasPass($metas),
                        new TransformerPass(
                            new DocumentNodeTraverser(new DefaultNodeTransformerFactory($metas))
                        )
                    ])
                ),
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
        $compileDocumentsCommand = new CompileDocumentsCommand($documents);

        /** @var DocumentNode[] $documents */
        $documents = $commandbus->handle($compileDocumentsCommand);

        $renderHandler = new RenderHandler(new DefaultTypeRendererFactory());
        $renderHandler->handle(new RenderCommand(
            HtmlTypeRenderer::TYPE,
            $documents,
            $metas,
            $sourceFileSystem,
            new Filesystem(new Local($outputPath)),
        ));
        $renderHandler->handle(new RenderCommand(
            IntersphinxTypeRenderer::TYPE,
            $documents,
            $metas,
            $sourceFileSystem,
            new Filesystem(new Local($outputPath)),
        ));

        foreach ($compareFiles as $compareFile) {
            $outputFile = str_replace($expectedPath, $outputPath, $compareFile);
            self::assertFileExists($outputFile);
            self::assertFileEquals($compareFile, $outputFile, 'output file does not match ' . $compareFile);
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
            ->in(__DIR__ . '/tests');

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
