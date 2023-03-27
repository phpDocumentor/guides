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

namespace phpDocumentor\Guides\Nodes;

use phpDocumentor\Guides\Span\InlineMarkupToken;

class SpanNode extends TextNode
{
    /** @var InlineMarkupToken[] */
    protected array $tokens;

    /** @param InlineMarkupToken[] $tokens */
    public function __construct(string $content, array $tokens = [])
    {
        parent::__construct($content);
        $this->tokens = $tokens;
    }

    /**
     * @return InlineMarkupToken[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }
}
