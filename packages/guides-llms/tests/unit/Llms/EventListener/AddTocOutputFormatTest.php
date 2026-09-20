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
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\ProjectSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddTocOutputFormat::class)]
final class AddTocOutputFormatTest extends TestCase
{
    public function testRendersTheTableOfContentsBesideWhateverElseIsRendered(): void
    {
        self::assertSame(['rst', 'toc'], $this->outputFormatsAfterListening(['rst']));
    }

    public function testDoesNotAddTheFormatTwiceToAProjectThatNamedItItself(): void
    {
        self::assertSame(['html', 'toc'], $this->outputFormatsAfterListening(['html', 'toc']));
    }

    /**
     * @param list<string> $outputFormats
     *
     * @return list<string>
     */
    private function outputFormatsAfterListening(array $outputFormats): array
    {
        $settings = new ProjectSettings();
        $settings->setOutputFormats($outputFormats);

        (new AddTocOutputFormat())(new PostProjectNodeCreated(new ProjectNode(), $settings));

        return $settings->getOutputFormats();
    }
}
