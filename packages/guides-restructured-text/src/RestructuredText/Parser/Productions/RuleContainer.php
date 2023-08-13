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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;

final class RuleContainer
{
    /** @var Rule<Node>[] */
    private array $productions;

    /** @param Rule<Node> ...$productions */
    public function __construct(Rule ...$productions)
    {
        $this->productions = $productions;
    }

    /** @param Rule<Node> $production */
    public function push(Rule $production): void
    {
        $this->productions[] = $production;
    }

    /** @param CompoundNode<Node> $on */
    public function apply(BlockContext $blockContext, CompoundNode $on): void
    {
        $documentIterator = $blockContext->getDocumentIterator();

        foreach ($this->productions as $production) {
            if (!$production->applies($blockContext)) {
                continue;
            }

            $newNode = $production->apply($blockContext, $on);
            if ($newNode !== null) {
                $on->addChildNode($newNode);
            }

            // TODO: Change the handling of detecting MetaNodes in form of
            // field lists after the subparser handling has been refactored.
            if ($production instanceof TitleRule && $on instanceof DocumentNode) {
                $on->setTitleFound(true);
            }

            break;
        }

        $documentIterator->next();
    }

    public function merge(RuleContainer $productions): self
    {
        return new self(
            ...$this->productions,
            ...$productions->productions,
        );
    }
}
