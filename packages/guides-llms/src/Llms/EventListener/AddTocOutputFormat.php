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

namespace phpDocumentor\Guides\Llms\EventListener;

use phpDocumentor\Guides\Event\PostProjectNodeCreated;
use phpDocumentor\Guides\Llms\Renderer\TocRenderer;

use function in_array;

/**
 * Render the table of contents beside whatever else the project renders.
 *
 * Enabling the extension is meant to be the whole of enabling the file, so the
 * format has to end up in the project's output formats without the project
 * having to name it. It cannot be put there through the configuration: an
 * explicitly configured "output-format" replaces the default ["html",
 * "interlink"] rather than extending it, so a project that configures nothing
 * would end up rendering a table of contents and no HTML.
 *
 * This is the last moment the list can still be added to -- the settings are
 * final by now, the CLI options included, and the render command reads them
 * afterwards -- so appending here leaves every other format untouched. A
 * project rendering only "rst" keeps doing that and gains the table of
 * contents beside it, and one that named "toc" itself does not get it twice.
 */
final class AddTocOutputFormat
{
    public function __invoke(PostProjectNodeCreated $event): void
    {
        $settings = $event->getSettings();

        $outputFormats = $settings->getOutputFormats();
        if (in_array(TocRenderer::FORMAT, $outputFormats, true)) {
            return;
        }

        $outputFormats[] = TocRenderer::FORMAT;
        $settings->setOutputFormats($outputFormats);
    }
}
