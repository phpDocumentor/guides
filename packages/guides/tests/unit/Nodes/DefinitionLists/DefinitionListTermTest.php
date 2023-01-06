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

use Prophecy\PhpUnit\ProphecyTrait;
use phpDocumentor\Guides\Nodes\SpanNode;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass \phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode
 * @covers ::<private>
 */
final class DefinitionListTermTest extends TestCase
{
    use ProphecyTrait;
    /**
     * @covers ::__construct
     * @covers ::getTerm
     */
    public function testTheDefinitionTermTextIsAvailable(): void
    {
        $term = $this->prophesize(SpanNode::class)->reveal();

        $definitionListTerm = new DefinitionListItemNode($term, [], []);

        self::assertSame($term, $definitionListTerm->getTerm());
    }

    /**
     * @covers ::__construct
     * @covers ::getClassifiers
     */
    public function testClassifiersAreMadeAvailable(): void
    {
        $term = $this->prophesize(SpanNode::class)->reveal();
        $classifier = $this->prophesize(SpanNode::class)->reveal();

        $definitionListTerm = new DefinitionListItemNode($term, [$classifier], []);

        self::assertSame([$classifier], $definitionListTerm->getClassifiers());
    }

    /**
     * @covers ::__construct
     * @covers ::getDefinitions
     * @covers ::getFirstDefinition
     */
    public function testDefinitionsAreMadeAvailable(): void
    {
        $term = $this->prophesize(SpanNode::class)->reveal();
        $definition1 = new DefinitionNode([]);
        $definition2 = new DefinitionNode([]);

        $definitionListTerm = new DefinitionListItemNode($term, [], [$definition1, $definition2]);

        self::assertSame([$definition1, $definition2], $definitionListTerm->getChildren());
    }
}
