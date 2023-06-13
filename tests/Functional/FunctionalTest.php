<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Functional;

use Exception;
use Gajus\Dindent\Indenter;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use phpDocumentor\Guides\ApplicationTestCase;
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Parser;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UrlGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Finder\Finder;
use Throwable;

use function array_filter;
use function array_map;
use function array_shift;
use function assert;
use function explode;
use function file;
use function file_exists;
use function file_get_contents;
use function implode;
use function in_array;
use function rtrim;
use function setlocale;
use function sprintf;
use function str_replace;
use function strpos;
use function substr;
use function trim;

use const LC_ALL;

class FunctionalTest extends ApplicationTestCase
{
    private const SKIP_INDENTER_FILES = ['code-block-diff'];

    protected function setUp(): void
    {
        setlocale(LC_ALL, 'en_US.utf8');
    }

    /** @param list<string> $expectedLogs */
    #[DataProvider('getFunctionalTests')]
    public function testFunctional(
        string $file,
        string $format,
        string $rst,
        string $expected,
        bool $useIndenter = true,
        array $expectedLogs = [],
    ): void {
        $expectedLines = explode("\n", $expected);
        $firstLine     = $expectedLines[0];

        $skip = strpos($firstLine, 'SKIP') === 0;
        if ($skip) {
            array_shift($expectedLines);
            $expected = implode("\n", $expectedLines);
        }

        try {
            if (strpos($firstLine, 'Exception:') === 0) {
                /** @psalm-var class-string<Throwable> */
                $exceptionClass = str_replace('Exception: ', '', $firstLine);
                $this->expectException($exceptionClass);

                $expectedExceptionMessage = $expectedLines;
                unset($expectedExceptionMessage[0]);
                $expectedExceptionMessage = implode("\n", $expectedExceptionMessage);

                $this->expectExceptionMessage($expectedExceptionMessage);
            }

            $parser = $this->getContainer()->get(Parser::class);
            $document = $parser->parse($rst);

            $compiler = $this->getContainer()->get(Compiler::class);
            $compiler->run([$document], new CompilerContext(new ProjectNode()));

            $inputFilesystem = new Filesystem(new MemoryAdapter());
            $inputFilesystem->write('img/test-image.jpg', 'Some image');

            $renderer = $this->getContainer()->get(DelegatingNodeRenderer::class);
            $context = RenderContext::forDocument(
                $document,
                $inputFilesystem,
                $outfs = new Filesystem(new MemoryAdapter()),
                '',
                new UrlGenerator(),
                $format,
                new ProjectNode(),
            );

            $rendered = '';

            foreach ($document->getNodes() as $node) {
                $rendered .= $renderer->render(
                    $node,
                    $context,
                );
            }

            if ($format === 'html' && $useIndenter) {
                $indenter = new Indenter();
                $rendered = $indenter->indent($rendered);
            }

            if (isset($expectedExceptionMessage)) {
                return;
            }

            self::assertSame(
                $this->trimTrailingWhitespace($expected),
                $this->trimTrailingWhitespace($rendered),
            );

            $logHandler = $this->getContainer()->get(TestHandler::class);
            assert($logHandler instanceof TestHandler);

            $logRecords = array_map(
                static fn (array $log) => $log['level_name'] . ': ' . $log['message'],
                array_filter($logHandler->getRecords(), static fn (array $log) => $log['level'] >= Logger::WARNING),
            );
            self::assertEquals($expectedLogs, $logRecords);
        } catch (ExpectationFailedException $e) {
            if ($skip) {
                $this->markTestIncomplete(substr($firstLine, 5) ?: '');
            }

            throw $e;
        }

        $this->assertFalse($skip, 'Test passes while marked as SKIP.');
    }

    /** @return mixed[] */
    public static function getFunctionalTests(): array
    {
        $finder = new Finder();
        $finder
            ->directories()
            ->in(__DIR__ . '/tests');

        $tests = [];

        foreach ($finder as $dir) {
            $rstFilename = $dir->getPathname() . '/' . $dir->getFilename() . '.rst';
            if (!file_exists($rstFilename)) {
                throw new Exception(sprintf('Could not find functional test file "%s"', $rstFilename));
            }

            $rst = file_get_contents($rstFilename);
            $basename = $dir->getFilename();

            $formats = ['html'];

            $fileFinder = new Finder();
            $fileFinder
                ->files()
                ->in($dir->getPathname())
                ->notName('*.rst');
            foreach ($fileFinder as $file) {
                $format = $file->getExtension();
                if (!in_array($format, $formats, true)) {
                    continue;
                }

                if (strpos($file->getFilename(), $dir->getFilename()) !== 0) {
                    throw new Exception(
                        sprintf('Test filename "%s" does not match directory name', $file->getPathname()),
                    );
                }

                $expected = $file->getContents();

                $useIndenter = !in_array($basename, self::SKIP_INDENTER_FILES, true);

                $logFile = $file->getPath() . '/' . $file->getFilenameWithoutExtension() . '.log';
                $logs = file_exists($logFile) ? array_map(trim(...), file($logFile)) : [];

                $tests[$basename . '_' . $format] = [$basename, $format, $rst, trim($expected), $useIndenter, $logs];
            }
        }

        return $tests;
    }

    private function trimTrailingWhitespace(string $string): string
    {
        $lines = explode("\n", $string);

        $lines = array_map(static function (string $line): string {
            return rtrim($line);
        }, $lines);

        return trim(implode("\n", $lines));
    }
}
