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

namespace phpDocumentor\Guides\Graphs\Twig;

use phpDocumentor\Guides\Graphs\Renderer\DiagramRenderer;
use phpDocumentor\Guides\RenderContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function assert;

final class UmlExtension extends AbstractExtension
{
    private DiagramRenderer $diagramRenderer;

    /** @param iterable<string, DiagramRenderer> $renderers */
    public function __construct(iterable $renderers, string $rendererAlias)
    {
        foreach ($renderers as $alias => $renderer) {
            if ($alias !== $rendererAlias) {
                continue;
            }

            $this->diagramRenderer = $renderer;
        }
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uml', $this->uml(...), ['is_safe' => ['html'], 'needs_context' => true]),
        ];
    }

    /** @param array{env: RenderContext} $context */
    public function uml(array $context, string $source): string|null
    {
        $renderContext = $context['env'];
        assert($renderContext instanceof RenderContext);

        return $this->diagramRenderer->render($renderContext, $source);
    }
}
