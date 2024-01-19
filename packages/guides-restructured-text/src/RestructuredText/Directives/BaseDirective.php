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

use phpDocumentor\Guides\Nodes\GenericNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;

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
abstract class BaseDirective
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
     * @param BlockContext $blockContext the current document context with the content
     *    of the directive
     * @param Directive $directive parsed directive containing options and variable
     */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        return $this->processNode($blockContext, $directive)
            // Ensure options are always available
            ->withKeepExistingOptions($this->optionsToArray($directive->getOptions()));
    }

    /**
     * This can be overloaded to write a directive that just create one node for the
     * document, which is common
     *
     * The arguments are the same that process
     */
    public function processNode(
        BlockContext $blockContext,
        Directive $directive,
    ): Node {
        return new GenericNode($directive->getVariable(), $directive->getData());
    }

    /**
     * @param DirectiveOption[] $options
     *
     * @return array<string, scalar|null>
     */
    protected function optionsToArray(array $options): array
    {
        return array_map(static fn (DirectiveOption $option): bool|float|int|string|null => $option->getValue(), $options);
    }
}
