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
        string|null $text = null,
    ): void {
        $result = $this->docReferenceTextRole->processNode($this->parserContext, 'doc', $span, $span);

        self::assertInstanceOf(DocReferenceNode::class, $result);
        self::assertEquals($url, $result->getTargetReference(), 'DocumentLinks are different');
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
                'url' => 'mydomain:path/to/document',
            ],
            'doc role, anchor and custom text' => [
                'span' => 'link <mydomain:foo/subdoc#anchor>',
                'url' => 'mydomain:foo/subdoc#anchor',
                'text' => 'link',
            ],
            'doc role, with greater-than character in text' => [
                'span' => 'text->sometext <subdoc>',
                'url' => 'subdoc',
                'text' => 'text->sometext',
            ],
        ];
    }
}
