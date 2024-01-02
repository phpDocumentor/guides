<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\References\EmbeddedReferenceParser;

/** @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#embedded-uris-and-aliases */
abstract class AbstractReferenceTextRole implements TextRole
{
    use EmbeddedReferenceParser;

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): AbstractLinkInlineNode {
        $referenceData = $this->extractEmbeddedReference($content);

        return $this->createNode($referenceData->reference, $referenceData->text, $role);
    }

    abstract protected function createNode(string $referenceTarget, string|null $referenceName, string $role): AbstractLinkInlineNode;
}
