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

namespace phpDocumentor\Guides\Nodes\Inline;

use phpDocumentor\Guides\Nodes\SectionNode;

use function array_merge;

/**
 * CrossReferences are references outside a document. As parsing is file based normal references are in document,
 * refering to other documents.
 *
 * Supported formats
 *
 *     :ref:`link`
 *     :ref:`custom text <link>`
 *
 * Cross-references are resolved at the start of the rendering phase.
 */
final class ReferenceNode extends AbstractLinkInlineNode implements CrossReferenceNode
{
    final public const TYPE = 'ref';

    public function __construct(
        string $targetReference,
        string $value = '',
        private readonly string $interlinkDomain = '',
        private readonly string $linkType = SectionNode::STD_LABEL,
        private readonly string $prefix = '',
    ) {
        parent::__construct(self::TYPE, $targetReference, $value);
    }

    public function getLinkType(): string
    {
        return $this->linkType;
    }

    public function getInterlinkDomain(): string
    {
        return $this->interlinkDomain;
    }

    /** @return array<string, string> */
    public function getDebugInformation(): array
    {
        return array_merge(parent::getDebugInformation(), [
            'linkType' => $this->getLinkType(),
            'interlinkDomain' => $this->getInterlinkDomain(),
        ]);
    }

    public function getInterlinkGroup(): string
    {
        return $this->linkType;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
