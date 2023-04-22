<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\GenericNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive as DirectiveModel;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

use function array_map;

/**
 * A directive is like a function you can call or apply to a block
 * It looks like:
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
     * to do anything with the document, like tweaking nodes or change the parser context
     *
     * The node that directly follows the directive is also passed to it
     *
     * @param DocumentParserContext $documentParserContext the current document context with the content
     *    of the directive
     * @param DirectiveModel $directive parsed directive containing options and variable
     */
    public function process(
        DocumentParserContext $documentParserContext,
        DirectiveModel $directive,
    ): Node|null {
        $document = $documentParserContext->getDocument();

        return $this->processNode($documentParserContext, $directive->getVariable(), $directive->getData(), $directive->getOptions())
            // Ensure options are always available
            ->withOptions($this->optionsToArray($directive->getOptions()));
    }

    /**
     * This can be overloaded to write a directive that just create one node for the
     * document, which is common
     *
     * The arguments are the same that process
     *
     * @param mixed[] $options
     */
    public function processNode(
        DocumentParserContext $documentParserContext,
        string $variable,
        string $data,
        array $options,
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
        array $options,
    ): void {
    }

    /**
     * @param DirectiveOption[] $options
     *
     * @return array<string, scalar|null>
     */
    protected function optionsToArray(array $options): array
    {
        return array_map(static fn (DirectiveOption $option) => $option->getValue(), $options);
    }
}
