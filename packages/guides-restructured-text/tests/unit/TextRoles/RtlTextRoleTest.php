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

final class RtlTextRoleTest extends TestCase
{
    private RtlTextRole $subject;
    private DocumentParserContext $documentParserContext;

    public function setUp(): void
    {
        $this->documentParserContext = self::createMock(DocumentParserContext::class);
        $this->subject = new RtlTextRole();
    }

    public function testGetNameReturnsRtl(): void
    {
        self::assertSame('rtl', $this->subject->getName());
    }

    public function testProcessNodeReturnsGenericTextRoleInlineNodeWithRtlType(): void
    {
        $inline = $this->subject->processNode($this->documentParserContext, 'rtl', 'محتوى', 'محتوى');

        self::assertInstanceOf(GenericTextRoleInlineNode::class, $inline);
        self::assertSame('rtl', $inline->getRole());
        self::assertSame('محتوى', $inline->getContent());
    }
}
