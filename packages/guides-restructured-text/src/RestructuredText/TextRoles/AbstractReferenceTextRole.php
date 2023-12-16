<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\EmbeddedUriParser;

/** @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#embedded-uris-and-aliases */
abstract class AbstractReferenceTextRole implements TextRole
{
    use EmbeddedUriParser;

    /** @see https://regex101.com/r/htMn5p/1 */
    public const INTERLINK_REGEX = '/^([a-zA-Z0-9-_]+):(.*$)/';

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): AbstractLinkInlineNode {
        $parsed = $this->extractEmbeddedUri($content);

        return $this->createNode($parsed['uri'], $parsed['text'], $role);
    }

    abstract protected function createNode(string $referenceTarget, string|null $referenceName, string $role): AbstractLinkInlineNode;
}
