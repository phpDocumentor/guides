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

namespace phpDocumentor\Guides\Nodes\DefinitionLists;

use phpDocumentor\Guides\Nodes\InlineNode;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode */
final class DefinitionListTermTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getTerm
     */
    public function testTheDefinitionTermTextIsAvailable(): void
    {
        $term = $this->createStub(InlineNode::class);

        $definitionListTerm = new DefinitionListItemNode($term, [], []);

        self::assertSame($term, $definitionListTerm->getTerm());
    }

    /**
     * @covers ::__construct
     * @covers ::getClassifiers
     */
    public function testClassifiersAreMadeAvailable(): void
    {
        $term = $this->createStub(InlineNode::class);
        $classifier = $this->createStub(InlineNode::class);

        $definitionListTerm = new DefinitionListItemNode($term, [$classifier], []);

        self::assertSame([$classifier], $definitionListTerm->getClassifiers());
    }

    /** @covers ::__construct */
    public function testDefinitionsAreMadeAvailable(): void
    {
        $term = $this->createStub(InlineNode::class);
        $definition1 = new DefinitionNode([]);
        $definition2 = new DefinitionNode([]);

        $definitionListTerm = new DefinitionListItemNode($term, [], [$definition1, $definition2]);

        self::assertSame([$definition1, $definition2], $definitionListTerm->getChildren());
    }
}
