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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\ClassNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function array_map;
use function array_merge;
use function explode;

final class ClassDirective extends SubDirective
{
    public function getName(): string
    {
        return 'class';
    }

    /**
     * When the default domain contains a class directive, this directive will be shadowed. Therefore, Sphinx re-exports it as rst-class.
     *
     * See https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html#rstclass
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return ['rst-class'];
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $classes = explode(' ', $directive->getData());

        $normalizedClasses = array_map(
            static fn (string $class): string => (new AsciiSlugger())->slug($class)->lower()->toString(),
            $classes,
        );

        $collectionNode->setClasses($normalizedClasses);

        if ($collectionNode->getChildren() === []) {
            $classNode = new ClassNode($directive->getData());
            $classNode->setClasses($classes);

            return $classNode;
        }

        $this->setNodesClasses($collectionNode->getChildren(), $classes);

        return new CollectionNode($collectionNode->getChildren());
    }

    /**
     * @param Node[] $nodes
     * @param string[] $classes
     */
    private function setNodesClasses(array $nodes, array $classes): void
    {
        foreach ($nodes as $node) {
            $node->setClasses(array_merge($node->getClasses(), $classes));

            if (!($node instanceof DocumentNode)) {
                continue;
            }

            $this->setNodesClasses($node->getNodes(), $classes);
        }
    }
}
