<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\Metadata\TopicNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

use function strtolower;

class AbstractFieldListItemRule implements FieldListItemRule
{
    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'abstract';
    }

    public function apply(FieldListItemNode $fieldListItemNode, DocumentParserContext $documentParserContext): MetadataNode
    {
        return new TopicNode('abstract', $fieldListItemNode->getPlaintextContent());
    }
}
