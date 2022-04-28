<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeTransformer;

use Webmozart\Assert\Assert;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\References\InternalTarget;

class CollectLinkTargetsTransformer implements NodeTransformer
{
    /** @var Metas */
    private $metas;

    /** @var DocumentNode|null */
    private $currentDocument = null;

    public function __construct(Metas $metas)
    {
        $this->metas = $metas;
    }

    public function enterNode(Node $node): Node
    {
        if ($node instanceof DocumentNode) {
            $this->currentDocument = $node;
        } elseif ($node instanceof AnchorNode) {
            Assert::notNull($this->currentDocument);

            $this->metas->addLinkTarget(
                $node->getValueString(),
                new InternalTarget($this->currentDocument->getFilePath(), $node->getValueString())
            );
        }

        return $node;
    }

    public function leaveNode(Node $node): Node
    {
        if ($node instanceof DocumentNode) {
            $this->currentDocument = null;
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentNode || $node instanceof AnchorNode;
    }
}
