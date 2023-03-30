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

interface TemplateRenderer
{
    /**
     * @param RenderContext $context
     * @param array<string, mixed> $params
     */
    public function renderTemplate(RenderContext $context, string $template, array $params = []): string;
}
