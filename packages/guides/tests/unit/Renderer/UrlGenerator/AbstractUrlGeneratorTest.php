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

namespace phpDocumentor\Guides\Renderer\UrlGenerator;

use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use PHPUnit\Framework\TestCase;

final class AbstractUrlGeneratorTest extends TestCase
{
    public function testAnInternalUrlIsComputedOncePerDocument(): void
    {
        $urlGenerator = new class (self::createStub(DocumentNameResolverInterface::class)) extends AbstractUrlGenerator {
            public int $invocations = 0;

            public function generateInternalPathFromRelativeUrl(
                RenderContext $renderContext,
                string $canonicalUrl,
            ): string {
                $this->invocations++;

                return $renderContext->getOutputFilePath() . '|' . $canonicalUrl;
            }
        };

        $document = $this->createMock(RenderContext::class);
        $document->method('getOutputFilePath')->willReturn('directory/file.html');
        $document->method('getDestinationPath')->willReturn('');

        $nextDocument = $this->createMock(RenderContext::class);
        $nextDocument->method('getOutputFilePath')->willReturn('other/file.html');
        $nextDocument->method('getDestinationPath')->willReturn('');

        self::assertSame('directory/file.html|target.html', $urlGenerator->generateInternalUrl($document, 'target.html'));
        self::assertSame('directory/file.html|target.html', $urlGenerator->generateInternalUrl($document, 'target.html'));
        self::assertSame(1, $urlGenerator->invocations, 'the second link to the same target is answered from the memo');

        self::assertSame('directory/file.html|other.html', $urlGenerator->generateInternalUrl($document, 'other.html'));
        self::assertSame(2, $urlGenerator->invocations, 'another target in the same document is computed');

        self::assertSame('other/file.html|target.html', $urlGenerator->generateInternalUrl($nextDocument, 'target.html'));
        self::assertSame(3, $urlGenerator->invocations, 'the next document computes the first target again');
    }

    public function testAComputationThatRendersAnotherDocumentDoesNotLeaveItsResultBehind(): void
    {
        $urlGenerator = new class (self::createStub(DocumentNameResolverInterface::class)) extends AbstractUrlGenerator {
            public RenderContext|null $nestedDocument = null;

            private bool $nesting = false;

            public function generateInternalPathFromRelativeUrl(
                RenderContext $renderContext,
                string $canonicalUrl,
            ): string {
                if ($this->nestedDocument !== null && !$this->nesting) {
                    $this->nesting = true;
                    $this->generateInternalUrl($this->nestedDocument, $canonicalUrl);
                    $this->nesting = false;
                }

                return $renderContext->getOutputFilePath() . '|' . $canonicalUrl;
            }
        };

        $document = $this->createMock(RenderContext::class);
        $document->method('getOutputFilePath')->willReturn('directory/file.html');
        $document->method('getDestinationPath')->willReturn('');

        $nestedDocument = $this->createMock(RenderContext::class);
        $nestedDocument->method('getOutputFilePath')->willReturn('other/file.html');
        $nestedDocument->method('getDestinationPath')->willReturn('');

        $urlGenerator->nestedDocument = $nestedDocument;

        self::assertSame('directory/file.html|target.html', $urlGenerator->generateInternalUrl($document, 'target.html'));

        $urlGenerator->nestedDocument = null;

        self::assertSame(
            'other/file.html|target.html',
            $urlGenerator->generateInternalUrl($nestedDocument, 'target.html'),
            'the outer result must not be stored under the key the nested call left behind',
        );
    }
}
