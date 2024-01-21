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

final class SpanNode extends TextNode
{
    /** @param InlineCompoundNode[] $tokens */
    public function __construct(string $content, protected array $tokens = [])
    {
        parent::__construct($content);
    }

    /** @return InlineCompoundNode[] */
    public function getTokens(): array
    {
        return $this->tokens;
    }
}
