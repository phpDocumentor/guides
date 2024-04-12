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

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Nodes\DocumentNode;

use function array_filter;

/**
 * The TransformerPass is a special kind of CompilerPass that traverses all documents and
 * Calls the DocumentNodeTraverser for each.
 *
 * The TransformerPass cannot be injected as there must be one for each available priority of
 * NodeTransformer.
 */
final class TransformerPass implements CompilerPass
{
    public function __construct(
        private readonly DocumentNodeTraverser $documentNodeTraverser,
        private readonly int $priority,
    ) {
    }

    /** {@inheritDoc} */
    public function run(array $documents, CompilerContext $compilerContext): array
    {
        foreach ($documents as $key => $document) {
            if (!($document instanceof DocumentNode)) {
                continue;
            }

            $compilerContext = $compilerContext->withDocumentShadowTree($document);
            $documents[$key] = $this->documentNodeTraverser->traverse($document, $compilerContext);
        }

        return array_filter($documents, static fn ($document): bool => $document instanceof DocumentNode);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
