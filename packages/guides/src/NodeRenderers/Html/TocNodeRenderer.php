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

use phpDocumentor\Guides\Meta\EntryLegacy;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;
use phpDocumentor\Guides\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Webmozart\Assert\Assert;

use function count;
use function is_array;
use function ltrim;

final class TocNodeRenderer implements NodeRenderer
{
    private Renderer $renderer;
    private UrlGeneratorInterface $urlGenerator;
    private Metas $metas;

    public function __construct(
        Renderer $renderer,
        UrlGeneratorInterface $urlGenerator,
        Metas $metas
    ) {
        $this->renderer = $renderer;
        $this->urlGenerator = $urlGenerator;
        $this->metas = $metas;
    }

    public function render(Node $node, RenderContext $environment): string
    {
        Assert::isInstanceOf($node, TocNode::class);

        if ($node->getOption('hidden', false)) {
            return '';
        }

        return $this->renderer->render(
            'toc.html.twig',
            [
                'node' => $node,
            ]
        );
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode;
    }
}
