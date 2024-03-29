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

use phpDocumentor\Guides\Nodes\Node;

interface LinkInlineNode extends Node
{
    public function getTargetReference(): string;

    public function setUrl(string $url): void;

    public function getUrl(): string;

    /** @return array<string, string> */
    public function getDebugInformation(): array;
}
