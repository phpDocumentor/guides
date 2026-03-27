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

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the NodeTraversalTrait.
 */
final class NodeTraversalTraitTest extends TestCase
{
    use NodeTraversalTrait;

    private int $visitCount = 0;

    protected function setUp(): void
    {
        $this->visitCount = 0;
    }

    public function testTraversesShallowStructure(): void
    {
        $nodes = [
            $this->createMock(Node::class),
            $this->createMock(Node::class),
        ];

        $this->traverseNodes($nodes, function (Node $node): void {
            $this->visitCount++;
        });

        self::assertSame(2, $this->visitCount);
    }

    public function testTraversesNestedStructure(): void
    {
        $child = $this->createMock(Node::class);
        $parent = $this->createCompoundNodeMock([$child]);
        $root = $this->createCompoundNodeMock([$parent]);

        $this->traverseNodes([$root], function (Node $node): void {
            $this->visitCount++;
        });

        self::assertSame(3, $this->visitCount);
    }

    public function testStopsAtMaxDepth(): void
    {
        // Create a deeply nested structure (deeper than MAX_TRAVERSAL_DEPTH of 100)
        $deepestNode = $this->createMock(Node::class);
        $current = $deepestNode;

        // Create 105 levels of nesting
        for ($i = 0; $i < 105; $i++) {
            $current = $this->createCompoundNodeMock([$current]);
        }

        $this->traverseNodes([$current], function (Node $node): void {
            $this->visitCount++;
        });

        // Should stop at depth 100, so we should visit at most 101 nodes
        // (depth 0 through depth 100 inclusive)
        self::assertLessThanOrEqual(101, $this->visitCount);
        // But should visit more than just a few (sanity check)
        self::assertGreaterThan(50, $this->visitCount);
    }

    public function testHandlesNodesWithoutGetChildrenMethod(): void
    {
        $nodeWithoutChildren = $this->createMock(Node::class);
        // This mock doesn't have getChildren method

        $this->traverseNodes([$nodeWithoutChildren], function (Node $node): void {
            $this->visitCount++;
        });

        self::assertSame(1, $this->visitCount);
    }

    public function testExactlyDepth100IsVisited(): void
    {
        // Create exactly 100 levels of nesting (depth 0 to 99)
        // The deepest node is at depth 99, so all 100 nodes should be visited
        $deepestNode = $this->createMock(Node::class);
        $current = $deepestNode;

        // Create 99 more levels (total 100 nodes)
        for ($i = 0; $i < 99; $i++) {
            $current = $this->createCompoundNodeMock([$current]);
        }

        $this->traverseNodes([$current], function (Node $node): void {
            $this->visitCount++;
        });

        // All 100 nodes should be visited (depth 0 through 99)
        self::assertSame(100, $this->visitCount);
    }

    public function testDepth101IsNotVisited(): void
    {
        // Create 102 levels of nesting (depth 0 to 101)
        // Nodes at depth 101 should NOT be visited due to limit check at depth > 100
        $deepestNode = $this->createMock(Node::class);
        $current = $deepestNode;

        // Create 101 more levels (total 102 nodes)
        for ($i = 0; $i < 101; $i++) {
            $current = $this->createCompoundNodeMock([$current]);
        }

        $this->traverseNodes([$current], function (Node $node): void {
            $this->visitCount++;
        });

        // Should visit exactly 101 nodes (depth 0 through 100)
        // The node at depth 101 should be skipped
        self::assertSame(101, $this->visitCount);
    }

    /**
     * @param Node[] $children
     *
     * @return CompoundNode<Node>
     */
    private function createCompoundNodeMock(array $children): CompoundNode
    {
        $mock = $this->createMock(CompoundNode::class);
        $mock->method('getChildren')->willReturn($children);

        return $mock;
    }
}
