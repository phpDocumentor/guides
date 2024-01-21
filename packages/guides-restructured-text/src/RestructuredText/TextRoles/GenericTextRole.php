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

final class GenericTextRole extends BaseTextRole
{
    protected string $name = 'default';
    protected string|null $baseRole = null;

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): GenericTextRoleInlineNode {
        return new GenericTextRoleInlineNode($this->baseRole ?? $role, $content, $this->getClass());
    }

    public function getBaseRole(): string|null
    {
        return $this->baseRole;
    }

    public function setBaseRole(string|null $baseRole): void
    {
        $this->baseRole = $baseRole;
    }
}
