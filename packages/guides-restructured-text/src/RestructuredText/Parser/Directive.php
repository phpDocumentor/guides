<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;

/**
 * Represents the data contained in an arbitrary directive
 *
 * .. name:: data
 *    :option: value
 *    :option2: value 2
 *
 * A directive can be saved into a variable, the data can be empty:
 *
 * .. |variable| name::
 */
class Directive
{
    private InlineCompoundNode|null $dataNode = null;

    /** @param DirectiveOption[] $options */
    public function __construct(private readonly string $variable, private readonly string $name, private readonly string $data, private array $options = [])
    {
    }

    public function getVariable(): string
    {
        return $this->variable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): string
    {
        return $this->data;
    }

    /** @return DirectiveOption[] */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function addOption(DirectiveOption $value): void
    {
        $this->options[$value->getName()] = $value;
    }
    
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function getOption(string $name): DirectiveOption
    {
        return $this->options[$name] ?? new DirectiveOption($name, null);
    }

    public function getDataNode(): InlineCompoundNode|null
    {
        return $this->dataNode;
    }

    public function setDataNode(InlineCompoundNode|null $dataNode): void
    {
        $this->dataNode = $dataNode;
    }
}
