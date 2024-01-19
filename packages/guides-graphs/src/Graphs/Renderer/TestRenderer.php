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

namespace phpDocumentor\Guides\Graphs\Renderer;

use phpDocumentor\Guides\RenderContext;

use function md5;

final class TestRenderer implements DiagramRenderer
{
    public function render(RenderContext $renderContext, string $diagram): string|null
    {
        return md5($diagram);
    }
}
