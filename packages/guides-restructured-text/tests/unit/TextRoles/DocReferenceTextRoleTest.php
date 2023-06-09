<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use Monolog\Logger;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\ParserContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DocReferenceTextRoleTest extends TestCase
{
    private Logger $logger;
    private DocReferenceTextRole $docReferenceTextRole;
    private ParserContext&MockObject $parserContext;

    public function setUp(): void
    {
        $this->logger = new Logger('test');
        $this->parserContext = $this->createMock(ParserContext::class);
        $this->docReferenceTextRole = new DocReferenceTextRole($this->logger);
    }

    #[DataProvider('docReferenceProvider')]
    public function testDocReferenceIsParsedIntoDocReferenceNode(
        string $span,
        string $url,
        string|null $domain = null,
        string|null $anchor = null,
        string|null $text = null,
    ): void {
        $result = $this->docReferenceTextRole->processNode($this->parserContext, 'doc', $span);

        self::assertInstanceOf(DocReferenceNode::class, $result);
        self::assertEquals($url, $result->getDocumentLink(), 'DocumentLinks are different');
        self::assertEquals($domain, $result->getDomain(), 'Domains are different');
        self::assertEquals($anchor, $result->getAnchor(), 'Anchors are different');
        self::assertEquals($text ?? $url, $result->getText());
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
                'domain' => 'mydomain',
            ],
            'doc with anchor' => [
                'span' => 'foo/subdoc#anchor',
                'url' => 'foo/subdoc',
                'domain' => null,
                'anchor' => 'anchor',
            ],
            'doc with domain, role and anchor' => [
                'span' => 'mydomain:foo/subdoc#anchor',
                'url' => 'foo/subdoc',
                'domain' => 'mydomain',
                'anchor' => 'anchor',
            ],
            'doc role, anchor and custom text' => [
                'span' => 'link <mydomain:foo/subdoc#anchor>',
                'url' => 'foo/subdoc',
                'domain' => 'mydomain',
                'anchor' => 'anchor',
                'text' => 'link',
            ],
            'doc role, with double point in text' => [
                'span' => 'text: sometext <mydomain:foo/subdoc#anchor>',
                'url' => 'foo/subdoc',
                'domain' => 'mydomain',
                'anchor' => 'anchor',
                'text' => 'text: sometext',
            ],
        ];
    }
}
