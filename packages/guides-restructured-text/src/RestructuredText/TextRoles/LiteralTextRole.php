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

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * Role to create a literal block.
 *
 * A literal block is a block of text that is displayed as-is, without any formatting.
 *
 * Example:
 *
 * ```rest
 * :literal:`code`
 * ```
 */
final class LiteralTextRole extends BaseTextRole
{
    protected string $name = 'literal';

    /** @return string[] */
    public function getAliases(): array
    {
        return ['code'];
    }

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): GenericTextRoleInlineNode {
        return new GenericTextRoleInlineNode('literal', $rawContent, $this->getClass());
    }
}
