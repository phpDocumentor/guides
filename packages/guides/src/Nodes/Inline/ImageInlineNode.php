<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

/**
 * In some Markup languages like Markdown Images are always considered inline. They can appear anywhere in a paragraph.
 */
final class ImageInlineNode extends InlineNode
{
    public const TYPE = 'image';

    public function __construct(private readonly string $url, private readonly string $altText)
    {
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
}
