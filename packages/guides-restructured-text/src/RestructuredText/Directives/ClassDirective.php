<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\ClassNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\CollectionNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function array_map;
use function array_merge;
use function explode;

class ClassDirective extends SubDirective
{
    public function getName(): string
    {
        return 'class';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        DocumentNode $document,
        Directive $directive,
    ): Node|null {
        $classes = explode(' ', $directive->getData());

        $normalizedClasses = array_map(
            static fn (string $class): string => (new AsciiSlugger())->slug($class)->lower()->toString(),
            $classes,
        );

        $document->setClasses($normalizedClasses);

        if (!$document instanceof DocumentNode || $document->getNodes() === []) {
            $classNode = new ClassNode($directive->getData());
            $classNode->setClasses($classes);

            return $classNode;
        }

        $this->setNodesClasses($document->getNodes(), $classes);

        return new CollectionNode($document->getNodes());
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
