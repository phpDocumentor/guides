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

use phpDocumentor\Guides\Nodes\Inline\WhitespaceInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * Role for inserting a non-breaking space.
 *
 * Usage: :nbsp:`ignored content`
 *
 * The content is ignored; the role simply produces a non-breaking space.
 * This is an alternative to the ~ syntax (e.g., a~b).
 */
final class NbspTextRole extends BaseTextRole
{
    protected string $name = 'nbsp';

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): WhitespaceInlineNode {
        return new WhitespaceInlineNode();
    }
}
