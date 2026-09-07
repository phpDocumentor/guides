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

use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use PHPUnit\Framework\TestCase;

final class LtrTextRoleTest extends TestCase
{
    private LtrTextRole $subject;
    private DocumentParserContext $documentParserContext;

    public function setUp(): void
    {
        $this->documentParserContext = self::createMock(DocumentParserContext::class);
        $this->subject = new LtrTextRole();
    }

    public function testGetNameReturnsLtr(): void
    {
        self::assertSame('ltr', $this->subject->getName());
    }

    public function testProcessNodeReturnsGenericTextRoleInlineNodeWithLtrType(): void
    {
        $inline = $this->subject->processNode($this->documentParserContext, 'ltr', 'content', 'content');

        self::assertInstanceOf(GenericTextRoleInlineNode::class, $inline);
        self::assertSame('ltr', $inline->getRole());
        self::assertSame('content', $inline->getContent());
    }
}
