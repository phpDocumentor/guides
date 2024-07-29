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

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\References\EmbeddedReferenceParser;

/** @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#embedded-uris-and-aliases */
abstract class AbstractReferenceTextRole implements TextRole
{
    use EmbeddedReferenceParser;

    protected bool $useRawContent = false;

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): AbstractLinkInlineNode {
        $referenceData = $this->extractEmbeddedReference($this->useRawContent ? $rawContent : $content);

        return $this->createNode($referenceData->reference, $referenceData->text, $role);
    }

    abstract protected function createNode(string $referenceTarget, string|null $referenceName, string $role): AbstractLinkInlineNode;
}
