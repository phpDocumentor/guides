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
    /** @var array<string, int|string> */
    private array $loggerInformation = [];

    /** @param Node[] $children */
    public function __construct(private readonly Directive $directive, array $children = [])
    {
        parent::__construct($children);
    }

    public function getDirective(): Directive
    {
        return $this->directive;
    }

    /**
     * Source location info (file, line), set once the directive's content has
     * been fully parsed -- available to createNode() for directives that need
     * to log a warning about their own content, since createNode() itself has
     * no access to BlockContext.
     *
     * @param array<string, int|string> $loggerInformation
     */
    public function setLoggerInformation(array $loggerInformation): void
    {
        $this->loggerInformation = $loggerInformation;
    }

    /** @return array<string, int|string> */
    public function getLoggerInformation(): array
    {
        return $this->loggerInformation;
    }
}
