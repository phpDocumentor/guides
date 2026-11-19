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

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/** @extends CompoundNode<Node> */
final class DirectiveNode extends CompoundNode
{
    private DirectiveSourceLocation $sourceLocation;

    /** @var array<class-string<DirectiveNodeData>, DirectiveNodeData> */
    private array $data = [];

    /**
     * @param Node[] $children
     * @param string $rawContent the directive's content exactly as written, before
     *     it was parsed into $children -- available to createNode() for directives
     *     that need the literal source text (e.g. raw passthrough) rather than (or
     *     alongside) the parsed node tree
     */
    public function __construct(
        private readonly Directive $directive,
        array $children = [],
        private readonly string $rawContent = '',
    ) {
        parent::__construct($children);

        $this->sourceLocation = new DirectiveSourceLocation();
    }

    public function getDirective(): Directive
    {
        return $this->directive;
    }

    /**
     * Set once the directive's content has been fully parsed -- available to
     * createNode() for directives that need to log a warning about their own
     * content, since createNode() itself has no access to BlockContext.
     */
    public function setSourceLocation(DirectiveSourceLocation $sourceLocation): void
    {
        $this->sourceLocation = $sourceLocation;
    }

    public function getSourceLocation(): DirectiveSourceLocation
    {
        return $this->sourceLocation;
    }

    public function getRawContent(): string
    {
        return $this->rawContent;
    }

    public function addData(DirectiveNodeData $data): void
    {
        $this->data[$data::class] = $data;
    }

    /**
     * @param class-string<T> $type
     *
     * @return T|null
     *
     * @template T of DirectiveNodeData
     */
    public function getData(string $type): mixed
    {
        $data = $this->data[$type] ?? null;
        if ($data === null) {
            foreach ($this->data as $candidate) {
                if ($candidate instanceof $type) {
                    $data = $candidate;
                    break;
                }
            }
        }

        if ($data === null || !$data instanceof $type) {
            return null;
        }

        return $data;
    }
}
