<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use Monolog\Logger;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
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
    public function testReferenceIsParsedIntoRefReferenceNode(
        string $span,
        string $url,
        string|null $text = null,
    ): void {
        $result = $this->referenceTextRole->processNode($this->parserContext, 'doc', $span, $span);

        self::assertInstanceOf(ReferenceNode::class, $result);
        self::assertEquals($url, $result->getTargetReference(), 'ReferenceNames are different');
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
