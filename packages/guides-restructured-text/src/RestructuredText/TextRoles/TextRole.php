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

use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

interface TextRole
{
    public function getName(): string;

    /** @return string[] */
    public function getAliases(): array;

    /**
     * @param string $content the content with backslash escapes removed per spec
     * @param string $rawContent the raw content, including backslash escapes
     */
    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): InlineNodeInterface;
}
