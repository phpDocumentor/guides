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

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListItemNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use PHPUnit\Framework\TestCase;

use function array_map;

final class AttachedNodeRegistryTest extends TestCase
{
    public function testProvidersCanContributeMultipleFields(): void
    {
        $node = new DefinitionListItemNode(
            InlineCompoundNode::getPlainTextInlineNode('term'),
            [InlineCompoundNode::getPlainTextInlineNode('classifier')],
        );

        $registry = new SubNodeAccessorRegistry([
            new CoreProvider(),
            new class implements SubNodeAccessorProvider {
                public function register(SubNodeAccessorRegistry $registry): void
                {
                    $registry->register(DefinitionListItemNode::class, new SubnodeList([
                        new CallableAccessor('custom', static fn (DefinitionListItemNode $node) => InlineCompoundNode::getPlainTextInlineNode('custom'), static fn (DefinitionListItemNode $node, InlineCompoundNode $value) => $node),
                    ]));
                }
            },
        ]);

        $fields = $registry->getFields($node);
        self::assertNotNull($fields);
        self::assertSame(['term', 'classifiers', 'custom'], array_map(static fn (CallableAccessor $field): string => $field->name, $fields->all()));
        self::assertCount(3, [...$fields->all()[0]->items($node), ...$fields->all()[1]->items($node), ...$fields->all()[2]->items($node)]);
    }
}
