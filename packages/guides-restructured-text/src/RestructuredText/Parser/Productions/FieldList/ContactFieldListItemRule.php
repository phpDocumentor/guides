<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\ContactNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;

use function strtolower;

class ContactFieldListItemRule implements FieldListItemRule
{
    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'contact';
    }

    public function apply(FieldListItemNode $fieldListItemNode): MetadataNode
    {
        return new ContactNode($fieldListItemNode->getPlaintextContent());
    }
}
