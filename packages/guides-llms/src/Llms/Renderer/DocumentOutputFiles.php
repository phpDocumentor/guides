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

use phpDocumentor\Guides\Renderer\BaseTypeRenderer;
use phpDocumentor\Guides\Renderer\TypeRendererFactory;
use phpDocumentor\Guides\Settings\SettingsManager;
use Throwable;

/**
 * The files one page of the manual was rendered to.
 *
 * A page can exist several times over -- as ".html" and as ".md", say -- and a
 * reader should be able to follow a link to the form it wants rather than
 * having to know which extension turns a bare path into a file.
 *
 * Only the forms that were rendered are named: a link to a file that is not
 * there would be worse than no link. Which those are follows from the output
 * formats the project configured, minus the ones that write something other
 * than a file per page. A format is the extension it writes -- that is the
 * rule the whole library generates URLs by, @see
 * \phpDocumentor\Guides\Renderer\UrlGenerator\AbstractUrlGenerator::createFileUrl()
 * -- so "md" is named ".md" without this class having to have heard of
 * Markdown, and a format added by an extension is named the same way.
 *
 * The question is not peculiar to the table of contents, so this is a public
 * service under its own class name: anything else writing about pages can
 * inject it instead of keeping a second list of output formats that drifts
 * from this one.
 */
final class DocumentOutputFiles
{
    /** @var list<string>|null */
    private array|null $formats = null;

    public function __construct(
        private readonly SettingsManager $settingsManager,
        private readonly TypeRendererFactory $typeRendererFactory,
    ) {
    }

    /**
     * @param string $path the page within the manual, without an extension
     *
     * @return array<string, string> the page's file per format, in the order
     *     the project configured the formats
     */
    public function of(string $path): array
    {
        $files = [];
        foreach ($this->formats() as $format) {
            $files[$format] = $path . '.' . $format;
        }

        return $files;
    }

    /**
     * The configured output formats that write one file per page.
     *
     * "interlink" and "toc" write a single file for the whole manual, a
     * single-page format writes one file for all pages at once; none of them
     * gives a page a file of its own. What separates them from the rest is
     * that the others render document by document, which is what
     * {@see BaseTypeRenderer} does and what they are all built on.
     *
     * @return list<string>
     */
    private function formats(): array
    {
        if ($this->formats !== null) {
            return $this->formats;
        }

        $this->formats = [];
        foreach ($this->settingsManager->getProjectSettings()->getOutputFormats() as $format) {
            try {
                $renderer = $this->typeRendererFactory->getRenderSet($format);
            } catch (Throwable) {
                // A format nothing can render is the render run's problem to
                // report, not this file's; it simply has no files to name.
                continue;
            }

            if (!$renderer instanceof BaseTypeRenderer) {
                continue;
            }

            $this->formats[] = $format;
        }

        return $this->formats;
    }
}
