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

use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\Interlink\DefaultInterlinkParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DocReferenceTextRoleTest extends TestCase
{
    private DocReferenceTextRole $docReferenceTextRole;
    private DocumentParserContext&MockObject $documentParserContext;

    public function setUp(): void
    {
        $this->documentParserContext = $this->createMock(DocumentParserContext::class);
        $this->docReferenceTextRole = new DocReferenceTextRole(new DefaultInterlinkParser());
    }

    #[DataProvider('docReferenceProvider')]
    public function testDocReferenceIsParsedIntoDocReferenceNode(
        string $span,
        string $url,
        string|null $text = null,
        string $domain = '',
    ): void {
        $result = $this->docReferenceTextRole->processNode($this->documentParserContext, 'doc', $span, $span);

        self::assertInstanceOf(DocReferenceNode::class, $result);
        self::assertEquals($url, $result->getTargetReference(), 'DocumentLinks are different');
        self::assertEquals($domain, $result->getInterlinkDomain(), 'Interlink domains are different');
        self::assertEquals($text ?? '', $result->toString());
    }

    /** @return array<string, array<string, string|null>> */
    public static function docReferenceProvider(): array
    {
        return [
            'doc role x' => [
                'span' => 'x',
                'url' => 'x',
            ],
            'doc role' => [
                'span' => 'path/to/document',
                'url' => 'path/to/document',
            ],
            'doc role absolute' => [
                'span' => '/absolute/path/to/document',
                'url' => '/absolute/path/to/document',
            ],
            'doc with domain' => [
                'span' => 'mydomain:path/to/document',
                'url' => 'path/to/document',
                'text' => null,
                'domain' => 'mydomain',
            ],
            'doc role, anchor and custom text' => [
                'span' => 'link <mydomain:foo/subdoc#anchor>',
                'url' => 'foo/subdoc#anchor',
                'text' => 'link',
                'domain' => 'mydomain',
            ],
            'doc role, with greater-than character in text' => [
                'span' => 'text->sometext <subdoc>',
                'url' => 'subdoc',
                'text' => 'text->sometext',
            ],
        ];
    }
}
