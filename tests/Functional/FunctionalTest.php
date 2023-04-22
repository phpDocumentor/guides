<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Functional;

use Exception;
use Gajus\Dindent\Indenter;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use phpDocumentor\Guides\ApplicationTestCase;
use phpDocumentor\Guides\Configuration;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\Parser;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UrlGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Throwable;

use function array_map;
use function explode;
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
    private const RENDER_DOCUMENT_FILES = ['main-directive'];
    private const SKIP_INDENTER_FILES = ['code-block-diff'];

    protected function setUp(): void
    {
        setlocale(LC_ALL, 'en_US.utf8');
    }

    #[DataProvider('getFunctionalTests')]
    public function testFunctional(
        string $file,
        string $format,
        string $rst,
        string $expected,
        bool $useIndenter = true,
    ): void {
        $expectedLines = explode("\n", $expected);
        $firstLine     = $expectedLines[0];

        if (strpos($firstLine, 'SKIP') === 0) {
            $this->markTestIncomplete(substr($firstLine, 5) ?: '');
        }

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

        $renderer = $this->getContainer()->get(DelegatingNodeRenderer::class);
        $context = RenderContext::forDocument(
            $document,
            new Filesystem(new MemoryAdapter()),
            $outfs = new Filesystem(new MemoryAdapter()),
            '',
            new Metas(),
            new UrlGenerator(),
            $format,
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

                $tests[$basename . '_' . $format] = [$basename, $format, $rst, trim($expected), $useIndenter];
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
