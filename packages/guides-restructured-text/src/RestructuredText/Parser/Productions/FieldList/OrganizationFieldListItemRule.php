<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\Metadata\OrganizationNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;

use function strtolower;

final class OrganizationFieldListItemRule implements FieldListItemRule
{
    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'organization';
    }

    public function apply(FieldListItemNode $fieldListItemNode, BlockContext $blockContext): MetadataNode
    {
        return new OrganizationNode(
            $fieldListItemNode->getPlaintextContent(),
            $fieldListItemNode->getChildren(),
        );
    }
}
