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

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Guides\Nodes\SectionNode;

use function array_merge;
use function is_string;

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

    /** @param InlineNodeInterface[] $children */
    public function __construct(
        string $targetReference,
        string|array $children = [],
        private readonly string $interlinkDomain = '',
        private readonly string $linkType = SectionNode::STD_LABEL,
        private readonly string $prefix = '',
    ) {
        if (is_string($children)) {
            Deprecation::trigger(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1161',
                'Passing the content of %s as string is deprecated, pass an array of InlineNodeInterface instances instead. New signature: string $targetReference, $children, string $interlinkDomain, string $linkType, string $prefix',
                static::class,
            );

            $children = $children === '' ? [] : [new PlainTextInlineNode($children)];
        }

        parent::__construct(self::TYPE, $targetReference, $children);
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
