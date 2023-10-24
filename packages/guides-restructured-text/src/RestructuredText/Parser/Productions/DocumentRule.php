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
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use Webmozart\Assert\Assert;

use function implode;
use function md5;

/** @implements Rule<DocumentNode> */
final class DocumentRule implements Rule
{
    public function __construct(private readonly RuleContainer $structuralElements)
    {
    }

    public function applies(BlockContext $blockContext): bool
    {
        return $blockContext->getDocumentIterator()->atStart();
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): DocumentNode
    {
        Assert::nullOrIsInstanceOf($on, DocumentNode::class);

        $on ??= new DocumentNode(
            md5(implode("\n", $blockContext->getDocumentIterator()->toArray())),
            $blockContext->getDocumentParserContext()->getContext()->getCurrentFileName(),
        );

        $blockContext->getDocumentParserContext()->setDocument($on);
        $documentIterator = $blockContext->getDocumentIterator();

        // We explicitly do not use foreach, but rather the cursors of the DocumentIterator
        // this is done because we are transitioning to a method where a Substate can take the current
        // cursor as starting point and loop through the cursor
        while ($documentIterator->valid()) {
            $this->structuralElements->apply($blockContext, $on);
        }

        $on->setLinks($blockContext->getDocumentParserContext()->getLinks());

        return $on;
    }
}
