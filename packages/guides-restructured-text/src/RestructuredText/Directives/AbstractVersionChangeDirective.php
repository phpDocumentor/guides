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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Nodes\VersionChangeNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;

/** @see https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#directive-versionadded */
abstract class AbstractVersionChangeDirective extends SubDirective
{
    public function __construct(protected Rule $startingRule, private readonly string $type, private readonly string $label)
    {
        parent::__construct($startingRule);
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    final protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return $this->createNode(
            new DirectiveNode(
                $directive,
                $collectionNode->getChildren(),
            ),
        );
    }

    public function getName(): string
    {
        try {
            return parent::getName();
        } catch (\LogicException) {
            Deprecation::trigger(
                'phpdocumentor/guides-restructured-text',
                'TODO: link',
                sprintf(
                    'Directives without attributes are deprecated, consult the documentation for more information on how to update your directives. Directive: %s',
                    $this->type,
                ),
            );

            return $this->type;
        }
    }

    public function createNode(DirectiveNode $directiveNode): Node|null
    {
        return new VersionChangeNode(
            $this->type,
            $this->label,
            $directiveNode->getDirective()->getData(),
            $directiveNode->getChildren(),
        );
    }
}
