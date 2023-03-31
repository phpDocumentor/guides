<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use SplStack;
use Webmozart\Assert\Assert;

/** @implements NodeTransformer<DocumentNode|AnchorNode> */
final class CollectLinkTargetsTransformer implements NodeTransformer
{
    private Metas $metas;

    /** @var SplStack<DocumentNode> */
    private SplStack $documentStack;

    public function __construct(Metas $metas)
    {
        $this->metas = $metas;
        /*
         * TODO: remove stack here, as we should not have sub documents in this way, sub documents are
         *       now produced by the {@see \phpDocumentor\Guides\RestructuredText\MarkupLanguageParser::getSubParser}
         *       as this works right now in isolation includes do not work as they should.
         */
        $this->documentStack = new SplStack();
    }

    public function enterNode(Node $node): Node
    {
        if ($node instanceof DocumentNode) {
            $this->documentStack->push($node);
        } elseif ($node instanceof AnchorNode) {
            $currentDocument = $this->documentStack->top();
            Assert::notNull($currentDocument);

            $this->metas->addLinkTarget(
                $node->toString(),
                new InternalTarget($currentDocument->getFilePath(), $node->toString())
            );
        }

        return $node;
    }

    public function leaveNode(Node $node): Node
    {
        if ($node instanceof DocumentNode) {
            $this->documentStack->pop();
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentNode || $node instanceof AnchorNode;
    }
}
