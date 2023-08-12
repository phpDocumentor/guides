<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * This text role is extended by custom text roles that do not feature a base text role:
 *
 * ```
 * .. role:: custom
 *    :class: special
 *
 *    :custom:`interpreted text`
 *
 * ```
 */
class SpanTextRole extends BaseTextRole
{
    protected string $name = 'span';

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): GenericTextRoleInlineNode {
        return new GenericTextRoleInlineNode('span', $rawContent, $this->getClass());
    }
}
