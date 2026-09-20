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

namespace phpDocumentor\Guides\Llms\Renderer;

use Exception;
use phpDocumentor\Guides\Renderer\BaseTypeRenderer;
use phpDocumentor\Guides\Renderer\TypeRenderer;
use phpDocumentor\Guides\Renderer\TypeRendererFactory;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_key_exists;
use function sprintf;

/**
 * The integration fixtures render HTML and nothing else per page, so the
 * formats a page is named in when a project renders more, less, or something
 * unrenderable are covered here.
 */
#[CoversClass(DocumentOutputFiles::class)]
final class DocumentOutputFilesTest extends TestCase
{
    /**
     * @param list<string> $outputFormats
     * @param array<string, string> $expected
     */
    #[DataProvider('outputFormats')]
    public function testNamesOnlyTheFilesThePageWasRenderedTo(array $outputFormats, array $expected): void
    {
        $projectSettings = new ProjectSettings();
        $projectSettings->setOutputFormats($outputFormats);

        $outputFiles = new DocumentOutputFiles(
            new SettingsManager($projectSettings),
            $this->renderers(),
        );

        self::assertSame($expected, $outputFiles->of('Chapter/Page'));
    }

    /** @return iterable<string, array{list<string>, array<string, string>}> */
    public static function outputFormats(): iterable
    {
        yield 'a format is the extension it writes' => [
            ['html'],
            ['html' => 'Chapter/Page.html'],
        ];

        yield 'a format the library never heard of is named the same way' => [
            ['html', 'md'],
            ['html' => 'Chapter/Page.html', 'md' => 'Chapter/Page.md'],
        ];

        yield 'named in the order the project configured them' => [
            ['md', 'html'],
            ['md' => 'Chapter/Page.md', 'html' => 'Chapter/Page.html'],
        ];

        yield 'a format writing one file for the whole project names no page' => [
            ['html', 'interlink', 'toc'],
            ['html' => 'Chapter/Page.html'],
        ];

        yield 'a single page is not a file per page either' => [
            ['singlepage', 'toc'],
            [],
        ];

        yield 'a format nothing can render is left to the render run to report' => [
            ['html', 'invented'],
            ['html' => 'Chapter/Page.html'],
        ];
    }

    /**
     * The renderers of a project that writes HTML, Markdown, a single page, an
     * inventory and a table of contents.
     */
    private function renderers(): TypeRendererFactory
    {
        $filePerDocument = $this->createStub(BaseTypeRenderer::class);
        $wholeProject = $this->createStub(TypeRenderer::class);

        $renderers = [
            'html' => $filePerDocument,
            'md' => $filePerDocument,
            'singlepage' => $wholeProject,
            'interlink' => $wholeProject,
            'toc' => $wholeProject,
        ];

        return new class ($renderers) implements TypeRendererFactory {
            /** @param array<string, TypeRenderer> $renderers */
            public function __construct(private readonly array $renderers)
            {
            }

            public function getRenderSet(string $outputFormat): TypeRenderer
            {
                if (!array_key_exists($outputFormat, $this->renderers)) {
                    throw new Exception(sprintf('No render set found for output format "%s"', $outputFormat));
                }

                return $this->renderers[$outputFormat];
            }
        };
    }
}
