<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\ContainerNode;

/**
 * Divs a sub document in a div with a given class or set of classes.
 *
 * @link https://docutils.sourceforge.io/docs/ref/rst/directives.html#container
 */
class ContainerDirective extends SubDirective
{
    public function getName(): string
    {
        return 'container';
    }

    /** {@inheritDoc} */
    public function getAliases(): array
    {
        return ['div'];
    }

    /** {@inheritDoc} */
    public function processSub(
        DocumentNode $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        return (new ContainerNode($document->getChildren()))->withOptions(['class' => $data]);
    }
}
