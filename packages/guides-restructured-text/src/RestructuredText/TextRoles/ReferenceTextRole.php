<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;

class ReferenceTextRole extends AbstractReferenceTextRole
{
    final public const NAME = 'ref';

    public function getName(): string
    {
        return self::NAME;
    }

    /** @inheritDoc */
    public function getAliases(): array
    {
        return [];
    }

    /** @return ReferenceNode */
    protected function createNode(string $referenceTarget, string|null $referenceName): AbstractLinkInlineNode
    {
        return new ReferenceNode($referenceTarget, $referenceName ?? '');
    }
}
