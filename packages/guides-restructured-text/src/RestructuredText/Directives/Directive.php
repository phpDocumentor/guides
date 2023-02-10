<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\GenericNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * A directive is like a function you can call or apply to a block
 * Il looks like:
 *
 * .. function:: main
 *     :arg1: value
 *     :arg2: otherValue
 *
 *     Some block !
 *
 *  The directive can define variables, create special nodes or change
 *  the node that directly follows it
 */
abstract class Directive
{
    /**
     * Get the directive name
     */
    abstract public function getName(): string;

    /**
     * Allow a directive to be registered under multiple names.
     *
     * Aliases can be used for directives whose name has been deprecated or allows for multiple spellings.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * This is the function called by the parser to process the directive, it can be overloaded
     * to do anything with the document, like tweaking nodes or change the environment
     *
     * The node that directly follows the directive is also passed to it
     *
     * @param DocumentParserContext $documentParserContext the current document context with the content
     *    of the directive
     * @param string $variable the variable name of the directive
     * @param string $data the data of the directive (following ::)
     * @param mixed[] $options the array of options for this directive
     */
    public function process(
        DocumentParserContext $documentParserContext,
        string $variable,
        string                $data,
        array                 $options
    ): ?Node {
        $document = $documentParserContext->getDocument();

        $processNode = $this->processNode($documentParserContext, $variable, $data, $options)
            // Ensure options are always available
            ->withOptions($options);

        if ($variable !== '') {
            $document->addVariable($variable, $processNode);
            return null;
        }

        return $processNode;
    }

    /**
     * This can be overloaded to write a directive that just create one node for the
     * document, which is common
     *
     * The arguments are the same that process
     *
     * @param DocumentParserContext $documentParserContext
     * @param mixed[] $options
     */
    public function processNode(
        DocumentParserContext $documentParserContext,
        string $variable,
        string $data,
        array $options
    ): Node {
        $this->processAction($documentParserContext, $variable, $data, $options);

        return new GenericNode($variable, $data);
    }

    /**
     * This can be overloaded to write a directive that just do an action without changing
     * the nodes of the document
     *
     * The arguments are the same that process
     *
     * @param mixed[] $options
     */
    public function processAction(
        DocumentParserContext $documentParserContext,
        string $variable,
        string $data,
        array $options
    ): void {
    }
}
