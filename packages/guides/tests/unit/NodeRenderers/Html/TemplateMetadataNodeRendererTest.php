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

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\Nodes\Metadata\NavigationTitleNode;
use phpDocumentor\Guides\Nodes\Metadata\TemplateNode;
use PHPUnit\Framework\TestCase;

final class TemplateMetadataNodeRendererTest extends TestCase
{
    private TemplateMetadataNodeRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new TemplateMetadataNodeRenderer();
    }

    public function test_it_supports_only_template_nodes(): void
    {
        self::assertTrue($this->renderer->supports(TemplateNode::class));
        self::assertFalse($this->renderer->supports(NavigationTitleNode::class));
    }
}
