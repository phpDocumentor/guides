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

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefinitionListItemNode::class)]
#[CoversMethod(DefinitionListItemNode::class, '__construct')]
#[CoversMethod(DefinitionListItemNode::class, 'getTerm')]
#[CoversMethod(DefinitionListItemNode::class, 'getClassifiers')]
final class DefinitionListTermTest extends TestCase
{
    public function testTheDefinitionTermTextIsAvailable(): void
    {
        $term = new InlineCompoundNode([]);

        $definitionListTerm = new DefinitionListItemNode($term, [], []);

        self::assertSame($term, $definitionListTerm->getTerm());
    }

    public function testClassifiersAreMadeAvailable(): void
    {
        $term = new InlineCompoundNode([]);
        $classifier = new InlineCompoundNode([]);

        $definitionListTerm = new DefinitionListItemNode($term, [$classifier], []);

        self::assertSame([$classifier], $definitionListTerm->getClassifiers());
    }

    public function testDefinitionsAreMadeAvailable(): void
    {
        $term = new InlineCompoundNode([]);
        $definition1 = new DefinitionNode([]);
        $definition2 = new DefinitionNode([]);

        $definitionListTerm = new DefinitionListItemNode($term, [], [$definition1, $definition2]);

        self::assertSame([$definition1, $definition2], $definitionListTerm->getChildren());
    }
}
