<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;

use function preg_match;

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
    protected function createNode(string $referenceTarget, string|null $referenceName, string $role): AbstractLinkInlineNode
    {
        $pattern =  AbstractReferenceTextRole::INTERLINK_REGEX;
        if (preg_match($pattern, $referenceTarget, $matches)) {
            $interlinkDomain = $matches[1];
            $path = $matches[2];
        } else {
            $interlinkDomain = '';
            $path = $referenceTarget;
        }

        return new DocReferenceNode($path, $referenceName ?? '', $interlinkDomain);
    }
}
