<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Nodes\CollectionNode;

/**
 * Wraps a sub document in a div with a given class
 */
class Wrap extends SubDirective
{
    public function getName(): string
    {
        return 'wrap';
    }

    public function processSub(
        DocumentNode $document,
        string       $variable,
        string       $data,
        array        $options
    ): ?Node {
        return new CollectionNode($document->getChildren());
    }
}
