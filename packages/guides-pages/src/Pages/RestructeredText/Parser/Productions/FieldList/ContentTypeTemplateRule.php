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

namespace phpDocumentor\Guides\Pages\RestructeredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Pages\Nodes\Metadata\ContentTypeTemplateNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\FieldListItemRule;

use function strtolower;

/**
 * RST field-list rule that converts a `:page-template: path/to/template.html.twig`
 * entry into a {@see ContentTypeTemplateNode}.
 *
 * The stored template path overrides the collection-level `item-template`
 * configured in `guides.xml` for this specific item only.
 *
 * Example RST usage:
 *
 * ```rst
 * :page-template: structure/my-custom-item.html.twig
 * ```
 */
final class ContentTypeTemplateRule implements FieldListItemRule
{
    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'page-template';
    }

    public function apply(FieldListItemNode $fieldListItemNode, BlockContext $blockContext): MetadataNode|null
    {
        return new ContentTypeTemplateNode($fieldListItemNode->getPlaintextContent());
    }
}
