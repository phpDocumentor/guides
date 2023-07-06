<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use SplStack;
use Webmozart\Assert\Assert;

use function assert;

/** @implements NodeTransformer<DocumentNode|AnchorNode|SectionNode> */
final class CollectLinkTargetsTransformer implements NodeTransformer
{
    /** @var SplStack<DocumentNode> */
    private readonly SplStack $documentStack;

    public function __construct()
    {
        /*
         * TODO: remove stack here, as we should not have sub documents in this way, sub documents are
         *       now produced by the {@see \phpDocumentor\Guides\RestructuredText\MarkupLanguageParser::getSubParser}
         *       as this works right now in isolation includes do not work as they should.
         */
        $this->documentStack = new SplStack();
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if ($node instanceof DocumentNode) {
            $this->documentStack->push($node);
        } elseif ($node instanceof AnchorNode) {
            $currentDocument = $compilerContext->getDocumentNode();
            $parentSection = $compilerContext->getShadowTree()->getParent()?->getNode();
            assert($parentSection instanceof SectionNode);

            $compilerContext->getProjectNode()->addLinkTarget(
                $node->toString(),
                new InternalTarget(
                    $currentDocument->getFilePath(),
                    $node->toString(),
                    $parentSection->getTitle()->toString(),
                ),
            );
        } elseif ($node instanceof SectionNode) {
            $currentDocument = $this->documentStack->top();
            Assert::notNull($currentDocument);
            $anchor = $node->getTitle()->getId();
            $compilerContext->getProjectNode()->addLinkTarget(
                $anchor,
                new InternalTarget(
                    $currentDocument->getFilePath(),
                    $anchor,
                    $node->getTitle()->toString(),
                ),
            );
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): DocumentNode|AnchorNode|SectionNode
    {
        if ($node instanceof DocumentNode) {
            $this->documentStack->pop();
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentNode || $node instanceof AnchorNode || $node instanceof SectionNode;
    }

    public function getPriority(): int
    {
        // After MetasPass
        return 5000;
    }
}
