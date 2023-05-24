<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use Monolog\Logger;
use phpDocumentor\Guides\Nodes\InlineToken\ReferenceNode;
use phpDocumentor\Guides\ParserContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReferenceTextRoleTest extends TestCase
{
    private Logger $logger;
    private ReferenceTextRole $referenceTextRole;
    private ParserContext&MockObject $parserContext;

    public function setUp(): void
    {
        $this->logger = new Logger('test');
        $this->parserContext = $this->createMock(ParserContext::class);
        $this->referenceTextRole = new ReferenceTextRole($this->logger);
    }

    #[DataProvider('referenceProvider')]
    public function testReferenceIsParsedIntoDocReferenceNode(
        string $span,
        string $url,
        string|null $domain = null,
        string|null $text = null,
    ): void {
        $result = $this->referenceTextRole->processNode($this->parserContext, 'id', 'doc', $span);

        self::assertInstanceOf(ReferenceNode::class, $result);
        self::assertEquals($url, $result->getReferenceName(), 'ReferenceNames are different');
        self::assertEquals($domain, $result->getDomain(), 'Domains are different');
        self::assertEquals($text ?? $url, $result->getText());
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
            'ref role with domain' => [
                'span' => 'mydomain:title ref',
                'referenceName' => 'title ref',
                'domain' => 'mydomain',
            ],
            'ref role with domain and custom text' => [
                'span' => 'link <mydomain:something>',
                'referenceName' => 'something',
                'domain' => 'mydomain',
                'text' => 'link',
            ],
            'ref role colon in text' => [
                'span' => 'Text: with colon <mydomain:something>',
                'referenceName' => 'something',
                'domain' => 'mydomain',
                'text' => 'Text: with colon',
            ],
        ];
    }
}
