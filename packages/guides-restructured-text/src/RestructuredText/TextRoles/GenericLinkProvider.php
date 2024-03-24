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

use phpDocumentor\Guides\Nodes\SectionNode;

/**
 *  Role to create references to link targets.
 *
 *  Example:
 *
 * ```rest
 *  :ref:`label`
 * ```
 */
final class GenericLinkProvider
{
    /** @var array<string, string> */
    private array $textRoleLinkTypeMapping = [
        'ref' => SectionNode::STD_LABEL,
    ];
    /** @var array<string, string> */
    private array $prefixLinkTypeMapping = ['ref' => ''];

    public function addGenericLink(string $textRole, string $linkType, string $prefix = ''): void
    {
        $this->textRoleLinkTypeMapping[$textRole] = $linkType;
        $this->prefixLinkTypeMapping[$textRole] = $prefix;
    }

    /** @return string[] */
    public function getTextRoleLinkTypeMapping(): array
    {
        return $this->textRoleLinkTypeMapping;
    }

    public function getLinkType(string $textRole): string
    {
        return $this->textRoleLinkTypeMapping[$textRole] ?? SectionNode::STD_LABEL;
    }

    public function getLinkPrefix(string $textRole): string
    {
        return $this->prefixLinkTypeMapping[$textRole] ?? '';
    }
}
