<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\RestructuredText\Parser\Interlink\InterlinkParser;

class DocReferenceTextRole extends AbstractReferenceTextRole
{
    final public const NAME = 'doc';

    public function __construct(
        private readonly InterlinkParser $interlinkParser,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /** @inheritDoc */
    public function getAliases(): array
    {
        return [];
    }

    /** @return DocReferenceNode */
    protected function createNode(string $referenceTarget, string|null $referenceName, string $role): AbstractLinkInlineNode
    {
        $interlinkData = $this->interlinkParser->extractInterlink($referenceTarget);

        return new DocReferenceNode($interlinkData->reference, $referenceName ?? '', $interlinkData->interlink);
    }
}
