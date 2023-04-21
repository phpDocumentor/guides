<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

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
    private string $variable;

    private string $name;

    private string $data;

    /** @var DirectiveOption[] */
    private array $options;

    /** @param DirectiveOption[] $options */
    public function __construct(string $variable, string $name, string $data, array $options = [])
    {
        $this->variable = $variable;
        $this->name = $name;
        $this->data = $data;
        $this->options = $options;
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
}
