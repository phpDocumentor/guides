<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/**
 * @template TValue
 */
abstract class AbstractNode implements Node
{
    /** @var string[] */
    protected $classes = [];
    /** @var mixed[] */
    protected array $options = [];

    /** @var TValue */
    protected $value;

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param TValue $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return TValue
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param string[] $classes
     */
    public function setClasses(array $classes): void
    {
        $this->classes = $classes;
    }

    public function getClassesString(): string
    {
        return implode(' ', $this->classes);
    }

    /**
     * @param array<string, mixed> $options
     * @return static
     */
    public function withOptions(array $options): Node
    {
        $result = clone $this;
        $result->options = $options;

        return $result;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * @template TType as mixed
     * @param TType|null $default
     *
     * @return ($default is null ? mixed|null: TType|null)
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }
}
