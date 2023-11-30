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
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

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
            new TwigFunction('uml', $this->uml(...), ['is_safe' => ['html']]),
        ];
    }

    public function uml(string $source): string|null
    {
        return $this->diagramRenderer->render($source);
    }
}
