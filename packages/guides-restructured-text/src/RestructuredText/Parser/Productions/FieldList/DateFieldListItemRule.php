<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\DateNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

use function strtolower;

class DateFieldListItemRule implements FieldListItemRule
{
    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'date';
    }

    public function apply(FieldListItemNode $fieldListItemNode, DocumentParserContext $documentParserContext): MetadataNode
    {
        return new DateNode($fieldListItemNode->getPlaintextContent());
    }
}
