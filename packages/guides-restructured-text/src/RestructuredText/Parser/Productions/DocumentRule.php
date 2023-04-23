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
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

use function implode;
use function md5;

/** @implements Rule<DocumentNode> */
final class DocumentRule implements Rule
{
    public function __construct(private readonly RuleContainer $structuralElements)
    {
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $documentParser->getDocumentIterator()->atStart();
    }

    /** @param DocumentNode|null $on */
    public function apply(DocumentParserContext $documentParserContext, CompoundNode|null $on = null): Node|null
    {
        $on ??= new DocumentNode(
            md5(implode("\n", $documentParserContext->getDocumentIterator()->toArray())),
            $documentParserContext->getContext()->getCurrentFileName(),
        );

        $documentParserContext->setDocument($on);
        $documentIterator = $documentParserContext->getDocumentIterator();

        // We explicitly do not use foreach, but rather the cursors of the DocumentIterator
        // this is done because we are transitioning to a method where a Substate can take the current
        // cursor as starting point and loop through the cursor
        while ($documentIterator->valid()) {
            $this->structuralElements->apply($documentParserContext, $on);
        }

        return $on;
    }
}
