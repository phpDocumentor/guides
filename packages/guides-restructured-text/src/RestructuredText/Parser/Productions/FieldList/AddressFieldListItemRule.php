<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\AddressNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;

use function strtolower;

class AddressFieldListItemRule implements FieldListItemRule
{
    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'address';
    }

    public function apply(FieldListItemNode $fieldListItemNode): MetadataNode
    {
        return new AddressNode($fieldListItemNode->getPlaintextContent());
    }
}
