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

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ReferenceTextRoleTest extends TestCase
{
    private ReferenceTextRole $referenceTextRole;
    private DocumentParserContext&MockObject $documentParserContext;

    public function setUp(): void
    {
        $this->documentParserContext = $this->createMock(DocumentParserContext::class);
        $this->referenceTextRole = new ReferenceTextRole();
    }

    #[DataProvider('referenceProvider')]
    public function testReferenceIsParsedIntoRefReferenceNode(
        string $span,
        string $referenceName,
        string|null $text = null,
    ): void {
        $result = $this->referenceTextRole->processNode($this->documentParserContext, 'doc', $span, $span);

        self::assertInstanceOf(ReferenceNode::class, $result);
        self::assertEquals($referenceName, $result->getTargetReference(), 'ReferenceNames are different');
        self::assertEquals($text ?? '', $result->toString());
    }

    /** @return array<string, array<string, string|null>> */
    public static function referenceProvider(): array
    {
        return [
            'ref role x' => [
                'span' => 'x',
                'referenceName' => 'x',
            ],
            'ref role' => [
                'span' => 'title ref',
                'referenceName' => 'title ref',
            ],
            'ref role withcustom text' => [
                'span' => 'link <something>',
                'referenceName' => 'something',
                'text' => 'link',
            ],
        ];
    }
}
