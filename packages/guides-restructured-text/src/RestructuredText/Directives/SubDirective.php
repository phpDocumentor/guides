<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;

/**
 * A directive that parses the sub block and call the processSub that can
 * be overloaded, like :
 *
 * .. sub-directive::
 *      Some block of code
 *
 *      You can imagine anything here, like adding *emphasis*, lists or
 *      titles
 */
abstract class SubDirective extends Directive
{
    /**
     * @param string[] $options
     */
    final public function process(
        MarkupLanguageParser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ): void {
        $subParser = $parser->getSubParser();

        if ($node instanceof CodeNode) {
            $document = $subParser->parse($parser->getEnvironment(), $node->getValueString());
        } else {
            $document = $node;
        }

        $newNode = $this->processSub($parser, $document, $variable, $data, $options);

        if ($newNode === null) {
            return;
        }

        $document = $parser->getDocument();
        if ($variable !== '') {
            $document->addVariable($variable, $newNode);
        } else {
            $document->addNode($newNode);
        }
    }

    /**
     * @param string[] $options
     */
    public function processSub(
        MarkupLanguageParser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        return null;
    }
}
