<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\ClassNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\CollectionNode;
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

    /** {@inheritDoc} */
    public function processSub(
        DocumentNode $document,
        string $variable,
        string $data,
        array $options,
    ): Node|null {
        $classes = explode(' ', $data);

        $normalizedClasses = array_map(
            static fn (string $class): string => (new AsciiSlugger())->slug($class)->lower()->toString(),
            $classes,
        );

        $document->setClasses($normalizedClasses);

        if (!$document instanceof DocumentNode || $document->getNodes() === []) {
            $classNode = new ClassNode($data);
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
