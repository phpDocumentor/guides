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

use function array_merge;
use function is_string;

/**
 * Represents a link to document
 *
 * Supported formats
 * :doc:`foo`
 * :doc:`domain:foo`
 * :doc:`foo/subdoc#anchor`
 * :doc:`custom text <foo>`
 * :doc:`custom text <domain:foo/subdoc#anchor>`
 */
final class DocReferenceNode extends AbstractLinkInlineNode implements CrossReferenceNode
{
    final public const TYPE = 'doc';

    /** @param InlineNodeInterface[] $children */
    public function __construct(
        string $targetDocument,
        string|array $children = [],
        private readonly string $interlinkDomain = '',
    ) {
        if (is_string($children)) {
            Deprecation::trigger(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1161',
                'Passing the content of %s as string is deprecated, pass an array of InlineNodeInterface instances instead. New signature: string $targetDocument, array $children, string $interlinkDomain',
                static::class,
            );

            $children = $children === '' ? [] : [new PlainTextInlineNode($children)];
        }

        parent::__construct(self::TYPE, $targetDocument, $children);
    }

    public function getInterlinkDomain(): string
    {
        return $this->interlinkDomain;
    }

    /** @return array<string, string> */
    public function getDebugInformation(): array
    {
        return array_merge(parent::getDebugInformation(), [
            'interlinkDomain' => $this->getInterlinkDomain(),
        ]);
    }

    public function getInterlinkGroup(): string
    {
        return 'std:doc';
    }
}
