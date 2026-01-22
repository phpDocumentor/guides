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

namespace phpDocumentor\Guides\Functional;

use Exception;
use Gajus\Dindent\Indenter;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\ApplicationTestCase;
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Parser;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Finder\Finder;
use Throwable;

use function array_filter;
use function array_map;
use function array_shift;
use function array_values;
use function assert;
use function class_alias;
use function class_exists;
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

if (class_exists('League\Flysystem\Memory\MemoryAdapter')) {
    class_alias('League\Flysystem\Memory\MemoryAdapter', 'League\Flysystem\InMemory\InMemoryFilesystemAdapter');
}

final class FunctionalTest extends ApplicationTestCase
{
    private const SKIP_INDENTER_FILES = ['code-block-diff'];

    private const IGNORED_WARNINGS = ['Document has no title'];

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
            assert($parser instanceof Parser);
            $document = $parser->parse($rst)->withIsRoot(true);

            $compiler = $this->getContainer()->get(Compiler::class);
            assert($compiler instanceof Compiler);
            $projectNode = new ProjectNode();
            $compiler->run([$document], new CompilerContext($projectNode));

            $inputFilesystem = FlySystemAdapter::createFromFileSystem(new Filesystem(new InMemoryFilesystemAdapter()));
            $inputFilesystem->put('img/test-image.jpg', 'Some image');


            $projectSettings = new ProjectSettings();
            $projectSettings->setLinksRelative(false);

            $settingsManager = $this->createMock(SettingsManager::class);
            $settingsManager->method('getProjectSettings')->willReturn($projectSettings);

            /** @var NodeRenderer<Node> $renderer */
            $renderer = $this->getContainer()->get('phpdoc.guides.output_node_renderer');
            $context = RenderContext::forDocument(
                $document,
                [$document],
                $inputFilesystem,
                FlySystemAdapter::createFromFileSystem(new Filesystem(new InMemoryFilesystemAdapter())),
                '',
                $format,
                $projectNode,
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

            /** @var list<string> $logRecords */
            $logRecords = array_map(
                static fn (array|LogRecord $log) => $log['level_name'] . ': ' . $log['message'],
                array_filter($logHandler->getRecords(), static fn (array|LogRecord $log) => $log['level'] >= Logger::WARNING &&
                    !in_array($log['message'], self::IGNORED_WARNINGS, true)),
            );
            self::assertEquals($expectedLogs, array_values($logRecords));
        } catch (ExpectationFailedException $e) {
            if ($skip) {
                self::markTestIncomplete(substr($firstLine, 5) ?: '');
            }

            throw $e;
        }

        self::assertFalse($skip, 'Test passes while marked as SKIP.');
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
                $logs = [];
                if (file_exists($logFile)) {
                    $logFileContent = file($logFile);
                    self::assertIsArray($logFileContent);
                    $logs = array_map(trim(...), $logFileContent);
                }

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
