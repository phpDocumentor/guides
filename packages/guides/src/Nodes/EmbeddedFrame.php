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

final class EmbeddedFrame extends TextNode
{
    public function __construct(
        private readonly string $url,
    ) {
        parent::__construct($url);
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
