<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\RenderContext;

class DocReferenceResolver implements ReferenceResolver
{
    public final const PRIORITY = 0;

    public function resolve(LinkInlineNode $node, RenderContext $renderContext): bool
    {
        if (!$node instanceof DocReferenceNode) {
            return false;
        }

        $file = $node->getDocumentEntry()?->getFile();
        if ($file === null) {
            return false;
        }

        $node->setUrl($renderContext->relativeDocUrl($file));

        return true;
    }

    public static function getPriority(): int
    {
        return self::PRIORITY;
    }
}
