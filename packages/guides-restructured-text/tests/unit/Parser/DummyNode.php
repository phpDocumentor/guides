<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\Nodes\Node;

class DummyNode implements Node
{
    /** @var DirectiveOption[] $directiveOptions the array of options for this directive */
    private array $directiveOptions;

    /** @param DirectiveOption[] $directiveOptions */
    public function __construct(private string $name, private string $data, array $directiveOptions)
    {
        $this->directiveOptions = $directiveOptions;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return DirectiveOption[] */
    public function getDirectiveOptions(): array
    {
        return $this->directiveOptions;
    }

    /** @return array<string, scalar|null> */
    public function getOptions(): array
    {
        return [];
    }

    /** {@inheritDoc} */
    public function withOptions(array $options): Node
    {
        return $this;
    }

    public function hasOption(string $name): bool
    {
        return false;
    }

    /** {@inheritDoc} */
    public function setValue($value): void
    {
    }

    /** {@inheritDoc} */
    public function getValue()
    {
        return $this->data;
    }

    /** {@inheritDoc} */
    public function getClasses(): array
    {
        return [];
    }

    /** {@inheritDoc} */
    public function setClasses(array $classes): void
    {
    }

    public function getClassesString(): string
    {
        return '';
    }
}
