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

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

interface Renderer
{
    /**
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context = []): string;

    public function renderNode(Node $node, RenderContext $environment): string;

    public function renderDocument(DocumentNode $node, RenderContext $environment): string;
}
