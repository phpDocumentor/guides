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
    public function __construct(private readonly DiagramRenderer $diagramRenderer)
    {
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
