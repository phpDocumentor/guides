<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\Metadata\OrganizationNode;

use function strtolower;

class OrganizationFieldListItemRule implements FieldListItemRule
{
    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'organization';
    }

    public function apply(FieldListItemNode $fieldListItemNode): MetadataNode
    {
        return new OrganizationNode(
            $fieldListItemNode->getPlaintextContent(),
            $fieldListItemNode->getChildren(),
        );
    }
}
