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
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentHandler;
use phpDocumentor\Guides\Handlers\RenderHandler;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\DocumentNode;
use League\Tactician\Setup\QuickStart;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\DefaultTypeRendererFactory;
use phpDocumentor\Guides\Renderer\HtmlTypeRenderer;
use phpDocumentor\Guides\Renderer\IntersphinxTypeRenderer;
use phpDocumentor\Guides\Renderer\LatexTypeRenderer;
use phpDocumentor\Guides\Renderer\TypeRenderer;
use phpDocumentor\Guides\UrlGenerator;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Flyfinder\Finder;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\AbstractLogger;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use function setlocale;

use const LC_ALL;

class IntegrationTest extends TestCase
{

    protected function setUp(): void
    {
        setlocale(LC_ALL, 'en_US.utf8');
    }

    /**
     * @param String[] $compareFiles
     * @dataProvider getTestsForDirectoryTest
     */
    public function testHtmlIntegration(
        string $inputPath,
        string $expectedPath,
        string $outputPath,
        array $compareFiles
    ): void {
        system("rm -rf " . escapeshellarg($outputPath));
        self::assertDirectoryExists($inputPath);
        self::assertDirectoryExists($expectedPath);
        self::assertNotEmpty($compareFiles);
        system("mkdir " . escapeshellarg($outputPath));

        $metas = new Metas([]);
        $sourceFileSystem = new Filesystem(new Local(
            $inputPath
        ));

        $documents = $this->compile($metas, $sourceFileSystem, $outputPath);

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
            self::assertFileEqualsTrimmed($compareFile, $outputFile);
        }
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file. It ignores empty lines and whitespace at the start and end of each line
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileEqualsTrimmed(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $constraint = new IsEqual(self::getTrimmedFileContent($expected));

        static::assertThat(self::getTrimmedFileContent($actual), $constraint, $message);
    }

    public static function getTrimmedFileContent(string $file): string
    {
        $contentArray = explode("\n", file_get_contents($file));
        array_walk($contentArray, function (&$value) {
            $value = trim($value);
        });
        $contentArray = array_filter($contentArray, function ($value) {
            return $value !== '';
        });
        return implode("\n", $contentArray);
    }

    /**
     * @return DocumentNode[]
     */
    private function compile(
        Metas $metas,
        Filesystem $sourceFileSystem,
        string $outputPath) : array {
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
        $sourceFileSystem->addPlugin(new Finder());

        $parseDirCommand = new ParseDirectoryCommand(
            $sourceFileSystem,
            '',
            'rst'
        );

        $documents = $parseDirectoryHandler->handle($parseDirCommand);
        $compileDocumentsCommand = new CompileDocumentsCommand($documents);

        /** @var DocumentNode[] $documents */
        return $commandbus->handle($compileDocumentsCommand);
    }

    /**
     * @return mixed[]
     */
    public function getTestsForDirectoryTest(): array
    {
        return $this->getTestsForDirectory();
    }

    /**
     * @return mixed[]
     */
    public function getTestsForLatex(): array
    {
        return $this->getTestsForDirectory('/tests-latex');
    }

    /**
     * @return mixed[]
     */
    private function getTestsForDirectory(string $directory = '/tests'): array
    {
        $finder = new SymfonyFinder();
        $finder
            ->directories()
            ->in(__DIR__ . $directory)
            ->depth('== 0');

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
                $tests[$dir->getPathname()] = [
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
