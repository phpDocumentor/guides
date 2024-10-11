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

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Settings\SettingsManager;
use Psr\Log\LoggerInterface;

/** @implements NodeTransformer<MenuNode> */
final class TocNodeReplacementTransformer implements NodeTransformer
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        if (!$node instanceof TocNode) {
            return $node;
        }

        if (!$this->settingsManager->getProjectSettings()->isAutomaticMenu()) {
            return $node;
        }

        if ($node->hasOption('hidden')) {
            $this->logger->warning('The `.. toctree::` directive with option `:hidden:` is not supported in automatic-menu mode. ', $compilerContext->getLoggerInformation());

            return null;
        }

        $this->logger->warning('The `.. toctree::` directive is not supported in automatic-menu mode. Use `.. menu::` instead. ', $compilerContext->getLoggerInformation());
        $menuNode = new NavMenuNode($node->getMenuEntries());
        $menuNode = $menuNode->withOptions($node->getOptions());
        $menuNode = $menuNode->withCaption($node->getCaption());

        return $menuNode;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode;
    }

    public function getPriority(): int
    {
        return 20_000;
    }
}
