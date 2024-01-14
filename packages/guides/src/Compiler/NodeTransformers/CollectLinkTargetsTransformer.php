<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\MultipleLinkTargetsNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use SplStack;
use Webmozart\Assert\Assert;

/** @implements NodeTransformer<DocumentNode|AnchorNode|SectionNode> */
final class CollectLinkTargetsTransformer implements NodeTransformer
{
    /** @var SplStack<DocumentNode> */
    private readonly SplStack $documentStack;

    public function __construct(
        private readonly AnchorNormalizer $anchorReducer,
    ) {
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
            $title = null;
            if ($parentSection instanceof SectionNode) {
                $title = $parentSection->getTitle()->toString();
            }

            $anchorName = $this->anchorReducer->reduceAnchor($node->toString());
            $compilerContext->getProjectNode()->addLinkTarget(
                $anchorName,
                new InternalTarget(
                    $currentDocument->getFilePath(),
                    $node->toString(),
                    $title,
                ),
            );
        } elseif ($node instanceof LinkTargetNode) {
            $currentDocument = $this->documentStack->top();
            Assert::notNull($currentDocument);
            $anchor = $node->getId();
            $compilerContext->getProjectNode()->addLinkTarget(
                $anchor,
                new InternalTarget(
                    $currentDocument->getFilePath(),
                    $anchor,
                    $node->getLinkText(),
                    $node->getLinkType(),
                ),
            );
            if ($node instanceof MultipleLinkTargetsNode) {
                foreach ($node->getAdditionalIds() as $id) {
                    $compilerContext->getProjectNode()->addLinkTarget(
                        $id,
                        new InternalTarget(
                            $currentDocument->getFilePath(),
                            $id,
                            $node->getLinkText(),
                            $node->getLinkType(),
                        ),
                    );
                }
            }
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if ($node instanceof DocumentNode) {
            $this->documentStack->pop();
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentNode || $node instanceof AnchorNode || $node instanceof LinkTargetNode;
    }

    public function getPriority(): int
    {
        // After MetasPass
        return 5000;
    }
}
