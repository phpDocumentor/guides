<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Nodes\CollectionNode;
use phpDocumentor\Guides\RestructuredText\Nodes\ContainerNode;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function array_map;
use function explode;

class ClassDirective extends SubDirective
{
    public function getName(): string
    {
        return 'class';
    }

    public function processSub(
        Node   $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        $classes = explode(' ', $data);

        $normalizedClasses = array_map(
            static fn(string $class): string => (new AsciiSlugger())->slug($class)->lower()->toString(),
            $classes
        );

        $document->setClasses($normalizedClasses);

        if (!$document instanceof DocumentNode) {
            // do not handle empty class directives for now
            return null;
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
            $node->setClasses($classes);

            if (!($node instanceof DocumentNode)) {
                continue;
            }

            $this->setNodesClasses($node->getNodes(), $classes);
        }
    }
}
