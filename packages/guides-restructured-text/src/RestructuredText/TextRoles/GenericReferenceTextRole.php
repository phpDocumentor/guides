<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorReducer;
use phpDocumentor\Guides\RestructuredText\Parser\Interlink\InterlinkParser;

use function array_keys;

class GenericReferenceTextRole extends AbstractReferenceTextRole
{
    public function __construct(
        private readonly GenericLinkProvider $genericLinkProvider,
        private readonly AnchorReducer $anchorReducer,
        private readonly InterlinkParser $interlinkParser,
    ) {
    }

    public function getName(): string
    {
        return 'ref';
    }

    /** @inheritDoc */
    public function getAliases(): array
    {
        return array_keys($this->genericLinkProvider->getTextRoleLinkTypeMapping());
    }

    /** @return ReferenceNode */
    protected function createNode(string $referenceTarget, string|null $referenceName, string $role): AbstractLinkInlineNode
    {
        $linkType = $this->genericLinkProvider->getLinkType($role);
        $interlinkData = $this->interlinkParser->extractInterlink($referenceTarget);
        $reference = $this->anchorReducer->reduceAnchor($interlinkData->reference);

        return new ReferenceNode($reference, $referenceName ?? '', $interlinkData->interlink, $linkType);
    }
}
