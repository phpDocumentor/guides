<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;

class DocReferenceTextRole extends AbstractReferenceTextRole
{
    final public const NAME = 'doc';

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
    protected function createNode(string $referenceTarget, string|null $referenceName): AbstractLinkInlineNode
    {
        return new DocReferenceNode($referenceTarget, $referenceName ?? '');
    }
}
