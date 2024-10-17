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

namespace phpDocumentor\Guides\Markdown\Parsers\FrontMatter;

use phpDocumentor\Guides\Nodes\DocumentNode;

interface Parser
{
    public function field(): string;

    /** @param  array<string, mixed> $frontMatter */
    public function process(DocumentNode $document, mixed $value, array $frontMatter): void;
}
