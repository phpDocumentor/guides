<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\Metadata\RevisionNode;

use function strtolower;

class RevisionFieldListItemRule implements FieldListItemRule
{
    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'revision';
    }

    public function apply(FieldListItemNode $fieldListItemNode, DocumentNode $documentNode): MetadataNode
    {
        return new RevisionNode($fieldListItemNode->getPlaintextContent());
    }
}
