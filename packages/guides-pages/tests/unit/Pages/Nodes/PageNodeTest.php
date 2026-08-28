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

namespace phpDocumentor\Guides\Pages\Nodes;

use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Metadata\AuthorNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Pages\Nodes\Metadata\PageDestinationNode;
use PHPUnit\Framework\TestCase;

/** @covers \phpDocumentor\Guides\Pages\Nodes\PageNode */
final class PageNodeTest extends TestCase
{
    public function testFilePathIsStoredAndReturned(): void
    {
        $node = new PageNode('about/index', []);

        self::assertSame('about/index', $node->getFilePath());
    }

    public function testDefaultOutputPathEqualsFilePath(): void
    {
        $node = new PageNode('about/index', []);

        self::assertSame('about/index', $node->getOutputPath());
    }

    public function testOutputPathCanBeOverridden(): void
    {
        $node = new PageNode('about/index', []);
        $node->setOutputPath('custom/path');

        self::assertSame('custom/path', $node->getOutputPath());
    }

    public function testChildrenAreStored(): void
    {
        $title    = new TitleNode(new InlineCompoundNode([new PlainTextInlineNode('Hello')]), 1, 'hello');
        $section  = new SectionNode($title);
        $node     = new PageNode('about', [$section]);

        self::assertCount(1, $node->getChildren());
        self::assertSame($section, $node->getChildren()[0]);
    }

    public function testHeaderNodesCanBeAdded(): void
    {
        $node        = new PageNode('about', []);
        $destination = new PageDestinationNode('custom/dest');
        $author  = new AuthorNode('John Doe', []);
        $node->addHeaderNode($destination);
        $node->addHeaderNode($author);

        self::assertCount(1, $node->getHeaderNodes());
        self::assertSame($author, $node->getHeaderNodes()[0]);
    }

    public function testGetPageTitleReturnsNullWhenNoHeaderNodes(): void
    {
        $node = new PageNode('about', []);

        self::assertNull($node->getPageTitle());
    }

    public function testGetPageTitleSkipsPageDestinationNode(): void
    {
        $node        = new PageNode('about', []);
        $destination = new PageDestinationNode('custom/dest');
        $node->addHeaderNode($destination);

        // PageDestinationNode has an empty toString() placeholder — should still be null
        self::assertNull($node->getPageTitle());
    }

    public function testGetNodesFiltersByType(): void
    {
        $title   = new TitleNode(new InlineCompoundNode([new PlainTextInlineNode('Hello')]), 1, 'hello');
        $section = new SectionNode($title);
        $node    = new PageNode('about', [$title, $section]);

        $titles = $node->getNodes(TitleNode::class);
        self::assertCount(1, $titles);
        self::assertSame($title, $titles[0]);
    }
}
