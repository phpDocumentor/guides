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
 * Role to create a math block.
 *
 * A math block is a block of text that is displayed as-is, without any formatting.
 *
 * Example:
 *
 * ```rest
 * :math:`code`
 * ```
 */
final class MathTextRole extends BaseTextRole
{
    protected string $name = 'math';

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): GenericTextRoleInlineNode {
        return new GenericTextRoleInlineNode('math', $rawContent, $this->getClass());
    }
}
