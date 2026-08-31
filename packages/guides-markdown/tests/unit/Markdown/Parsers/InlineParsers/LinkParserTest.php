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

namespace phpDocumentor\Guides\Markdown\Parsers\InlineParsers;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\NodeWalker;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LinkParserTest extends TestCase
{
    private LinkParser $parser;

    protected function setUp(): void
    {
        $this->parser = new LinkParser([], $this->createMock(LoggerInterface::class));
    }

    #[DataProvider('urlProvider')]
    public function testMarkdownExtensionAndFragmentHandling(string $expected, string $url): void
    {
        $link = new Link($url);

        $result = $this->parser->parse(
            $this->createMock(MarkupLanguageParser::class),
            new NodeWalker($link),
            $link,
        );

        self::assertInstanceOf(HyperLinkNode::class, $result);
        self::assertSame($expected, $result->getTargetReference());
    }

    /** @return array<string, array{string, string}> */
    public static function urlProvider(): array
    {
        return [
            'no anchor' => [
                'expected' => 'page',
                'url' => 'page.md',
            ],
            'with anchor' => [
                'expected' => 'page#section-two',
                'url' => 'page.md#section-two',
            ],
            'duplicate anchor' => [
                'expected' => 'page#section-two#extra',
                'url' => 'page.md#section-two#extra',
            ],
            'anchor only, no page' => [
                'expected' => '#anchor',
                'url' => '#anchor',
            ],
            'fragment ends in extension' => [
                'expected' => 'page#anchor.md',
                'url' => 'page.md#anchor.md',
            ],
        ];
    }
}
