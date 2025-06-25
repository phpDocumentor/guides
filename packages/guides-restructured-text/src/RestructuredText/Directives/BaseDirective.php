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
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
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
    /** @var array<string, Option|null> Cache of Option attributes indexed by option name */
    private array $optionAttributeCache;

    private string $name;

    private array $aliases;

    /**
     * Get the directive name
     */
    public function getName(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }

        $reflection = new \ReflectionClass($this);
        $attributes = $reflection->getAttributes(Attributes\Directive::class);

        if (count($attributes) === 0) {
            throw new \LogicException('Directive class must have a Directive attribute');
        }

        $this->name = $attributes[0]->newInstance()->name;
        return $this->name;
    }

    /**
     * Allow a directive to be registered under multiple names.
     *
     * Aliases can be used for directives whose name has been deprecated or allows for multiple spellings.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        if (isset($this->aliases)) {
            return $this->aliases;
        }

        $reflection = new \ReflectionClass($this);
        $attributes = $reflection->getAttributes(Attributes\Directive::class);
        $this->aliases = [];
        if (count($attributes) !== 0) {
            $this->aliases = $attributes[0]->newInstance()->aliases;
        }

        return $this->aliases;
    }

    /**
     * Returns whether this directive has been upgraded to a new version.
     *
     * In the new version of directives, the processing is done during the compile phase.
     * This method only exists to allow for backward compatibility with directives that
     * were written before the upgrade.
     *
     * @internal
     */
    final public function isUpgraded(): bool
    {
        $reflection = new \ReflectionClass($this);
        $attributes = $reflection->getAttributes(Attributes\Directive::class);

        return count($attributes) === 1;
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

    public function createNode(Directive $directive): Node|null
    {
        return null;
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

    /**
     * Gets an option value from a directive based on attribute configuration.
     *
     * Looks up the option in the directive and returns its value converted to the
     * appropriate type based on the Option attribute defined on this directive class.
     * If the option is not present in the directive, returns the default value from the attribute.
     *
     * @param Directive $directive The directive containing the options
     * @param string $optionName The name of the option to retrieve
     *
     * @return mixed The option value converted to the appropriate type, or the default value
     */
    final protected function readOption(Directive $directive, string $optionName): mixed
    {
        $optionAttribute = $this->findOptionAttribute($optionName);

        return $this->getOptionValue($directive, $optionAttribute);
    }

    final protected function readAllOptions(Directive $directive): array
    {
        $this->initialize();

        return array_map(
            fn (Option $option) => $this->getOptionValue($directive, $option),
            $this->optionAttributeCache
        );
    }

    private function getOptionValue(Directive $directive, Option|null $option): mixed
    {
        if ($option === null) {
            return null;
        }

        if (!$directive->hasOption($option->name)) {
            return $option->default;
        }

        $directiveOption = $directive->getOption($option->name);
        $value = $directiveOption->getValue();

        return match ($option->type) {
            OptionType::Integer => (int) $value,
            OptionType::Boolean => $value === null || filter_var($value, FILTER_VALIDATE_BOOL),
            OptionType::String => (string) $value,
            OptionType::Array => (array) $value,
            default => $value,
        };
    }


    /**
     * Finds the Option attribute for the given option name on the current class.
     *
     * @param string $optionName The option name to look for
     *
     * @return Option|null The Option attribute if found, null otherwise
     */
    private function findOptionAttribute(string $optionName): ?Option
    {
        $this->initialize();

        return $this->optionAttributeCache[$optionName] ?? null;
    }

    private function initialize(): void
    {
        if (isset($this->optionAttributeCache)) {
            return;
        }

        $reflection = new \ReflectionClass($this);
        $attributes = $reflection->getAttributes(Option::class);
        $this->optionAttributeCache = [];
        foreach ($attributes as $attribute) {
            $option = $attribute->newInstance();
            $this->optionAttributeCache[$option->name] = $option;
        }
    }
}


