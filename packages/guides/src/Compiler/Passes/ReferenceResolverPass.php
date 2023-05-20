<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\ReferenceNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;

use phpDocumentor\Guides\Nodes\SpanNode;
use Symfony\Component\String\Slugger\AsciiSlugger;
use function array_map;
use function array_merge;
use function current;
use function in_array;
use function key;
use function next;
use function prev;

/**
 * Resolves reference tokens in span nodes
 *
 * This follows the reStructuredText rules as outlined in:
 * https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#implicit-hyperlink-targets
 */
class ReferenceResolverPass implements CompilerPass
{
    public function getPriority(): int
    {
        return 10000; // must be run *after* MetasPass
    }

    public function __construct(private readonly Metas $metas)
    {
    }

    /** {@inheritDoc} */
    public function run(array $documents): array
    {
        foreach ($documents as $document) {
            $this->traverse($document, $document->getFilePath());
        }

        return $documents;
    }

    private function traverse(Node $node, string $path): void
    {
        if (!($node instanceof CompoundNode)) {
            return;
        }

        foreach ($node->getChildren() as $child) {
            if ($child instanceof SpanNode) {
                foreach ($child->getTokens() as $token) {
                    if ($token instanceof ReferenceNode) {
                        $this->resolveReference($token);
                    } else if ($token instanceof DocReferenceNode) {
                        $this->resolveDocReference($token);

                    }
                }

            } elseif ($child instanceof CompoundNode) {
                $this->traverse($child, $path);
            }
        }
    }

    private function resolveReference(ReferenceNode $referenceNode): void {
        $referenceNode->setUrl('/todo.html');
    }

    private function resolveDocReference(DocReferenceNode $docReferenceNode): void {
        $docReferenceNode->setUrl('/todo.html');

    }
}
