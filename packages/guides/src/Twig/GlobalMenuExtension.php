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

namespace phpDocumentor\Guides\Twig;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use RuntimeException;
use Throwable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class GlobalMenuExtension extends AbstractExtension
{
    /**
     * Contains cached menu html for each render context to prevent multiple rendering of the same menu.
     *
     * @var array<string, string>
     */
    private array $menuCache = [];

    /** @param NodeRenderer<Node> $nodeRenderer */
    public function __construct(
        private readonly NodeRenderer $nodeRenderer,
    ) {
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderMenu', $this->renderMenu(...), ['is_safe' => ['html'], 'needs_context' => true]),
        ];
    }

    /** @param array{env: RenderContext} $context */
    public function renderMenu(array $context, string $menuType): string
    {
        $renderContext = $this->getRenderContext($context);
        $globalMenues = $renderContext->getProjectNode()->getGlobalMenues();
        if (isset($this->menuCache[$renderContext->getCurrentFileName() . '::' . $menuType])) {
            return $this->menuCache[$renderContext->getCurrentFileName() . '::' . $menuType];
        }

        $menues = [];
        foreach ($globalMenues as $menu) {
            $menu = $menu->withOptions(['menu' => $menuType]);
            try {
                $menu = $menu->withCurrentPath($renderContext->getCurrentFileName());
                $menu = $menu->withRootlinePaths($renderContext->getCurrentFileRootline());
            } catch (Throwable) {
                // do nothing, we are in a context without active menu like single page or functional test
            }

            $menues[] = $menu;
        }

        return $this->menuCache[$renderContext->getCurrentFileName() . '::' . $menuType] = $this->nodeRenderer->render(new CollectionNode($menues), $renderContext);
    }

    /** @param array{env: RenderContext} $context */
    private function getRenderContext(array $context): RenderContext
    {
        $renderContext = $context['env'] ?? null;
        if (!$renderContext instanceof RenderContext) {
            throw new RuntimeException('Render context must be set in the twig global state to render nodes');
        }

        return $renderContext;
    }
}
