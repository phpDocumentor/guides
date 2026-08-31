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

use DOMDocument;
use Exception;
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
use function assert;
use function explode;
use function file;
use function file_exists;
use function file_get_contents;
use function implode;
use function in_array;
use function is_string;
use function libxml_clear_errors;
use function libxml_use_internal_errors;
use function preg_replace;
use function setlocale;
use function sprintf;
use function str_replace;
use function strpos;
use function substr;
use function trim;

use const LC_ALL;

final class FunctionalTest extends ApplicationTestCase
{
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
            [$document] = $compiler->run([$document], new CompilerContext($projectNode));

            $inputFilesystem = FlySystemAdapter::createInMemory();
            $inputFilesystem->put('img/test-image.jpg', 'Some image');

            $projectSettings = new ProjectSettings();
            $projectSettings->setLinksRelative(false);

            $settingsManager = new SettingsManager($projectSettings);

            /** @var NodeRenderer<Node> $renderer */
            $renderer = $this->getContainer()->get('phpdoc.guides.output_node_renderer');
            $context = RenderContext::forDocument(
                $document,
                [$document],
                $inputFilesystem,
                FlySystemAdapter::createInMemory(),
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

            if (isset($expectedExceptionMessage)) {
                return;
            }

            if ($format === 'html') {
                $rendered = $this->removeRedundantWhitespaceFromHtml($rendered);
                $expected = $this->removeRedundantWhitespaceFromHtml($expected);

                $previousUseInternalErrors = libxml_use_internal_errors(true);
                try {
                    $expectedDom = new DOMDocument();
                    $expectedDom->loadHTML($expected);
                    $expectedDom->preserveWhiteSpace = false;

                    $actualDom = new DOMDocument();
                    $actualDom->loadHTML($rendered);
                    $actualDom->preserveWhiteSpace = false;

                    $expectedHtml = $expectedDom->saveHTML();
                    $actualHtml = $actualDom->saveHTML();

                    self::assertIsString($expectedHtml);
                    self::assertIsString($actualHtml);

                    self::assertXmlStringEqualsXmlString($expectedHtml, $actualHtml);
                } catch (Throwable) {
                    self::assertSame(trim($expected), trim($rendered));
                } finally {
                    libxml_clear_errors();
                    libxml_use_internal_errors($previousUseInternalErrors);
                }
            } else {
                self::assertSame(trim($expected), trim($rendered));
            }

            $logHandler = $this->getContainer()->get(TestHandler::class);
            assert($logHandler instanceof TestHandler);

            /** @var list<string> $logRecords */
            $logRecords = [];
            foreach (
                array_filter(
                    $logHandler->getRecords(),
                    static fn (array|LogRecord $log): bool => $log['level'] >= Logger::WARNING &&
                        !in_array($log['message'], self::IGNORED_WARNINGS, true),
                ) as $log
            ) {
                $logRecords[] = (is_string($log['level_name']) ? $log['level_name'] : '') . ': ' . (is_string($log['message']) ? $log['message'] : '');
            }

            self::assertEquals($expectedLogs, $logRecords);
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

                $logFile = $file->getPath() . '/' . $file->getFilenameWithoutExtension() . '.log';
                $logs = [];
                if (file_exists($logFile)) {
                    $logFileContent = file($logFile);
                    self::assertIsArray($logFileContent);
                    $logs = array_map(trim(...), $logFileContent);
                }

                $tests[$basename . '_' . $format] = [$basename, $format, $rst, trim($expected), $logs];
            }
        }

        return $tests;
    }

    private function removeRedundantWhitespaceFromHtml(string $html): string
    {
        $html = implode("\n", array_map('trim', explode("\n", $html)));
        $html = preg_replace('#\s+#', ' ', $html) ?? $html;
        $html = preg_replace('#\s<#', '<', $html) ?? $html;
        $html = preg_replace('#>\s#', '>', $html) ?? $html;

        return $html;
    }
}
