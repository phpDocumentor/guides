<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorReducer;
use Psr\Log\LoggerInterface;

use function array_keys;

class GenericReferenceTextRole extends AbstractReferenceTextRole
{
    public function __construct(
        protected readonly LoggerInterface $logger,
        private readonly GenericLinkProvider $genericLinkProvider,
        private readonly AnchorReducer $anchorReducer,
    ) {
        parent::__construct($this->logger);
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
        $id = $this->anchorReducer->reduceAnchor($referenceTarget);

        return new ReferenceNode($id, $referenceName ?? '', $linkType);
    }
}
