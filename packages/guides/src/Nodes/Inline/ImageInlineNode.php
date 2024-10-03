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

/**
 * In some Markup languages like Markdown Images are always considered inline. They can appear anywhere in a paragraph.
 */
final class ImageInlineNode extends InlineNode
{
    public const TYPE = 'image';

    public function __construct(
        private readonly string $url,
        private readonly string $altText,
        private readonly string|null $title = null,
    ) {
        parent::__construct(self::TYPE, $url);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAltText(): string
    {
        return $this->altText;
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }
}
