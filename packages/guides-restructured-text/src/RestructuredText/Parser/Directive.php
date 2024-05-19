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
final class Directive
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

    public function getOptionString(string $name, string $default = ''): string
    {
        if (!isset($this->options[$name])) {
            return $default;
        }

        return $this->options[$name]->toString();
    }

    public function getOptionBool(string $name, bool $default = false, bool $nullDefault = true): bool
    {
        if (!isset($this->options[$name])) {
            return $default;
        }

        if ($this->options[$name]->getValue() === null) {
            return $nullDefault;
        }

        return $this->options[$name]->toBool();
    }

    public function getOptionInt(string $name, int $default = 0): int
    {
        if (!isset($this->options[$name])) {
            return $default;
        }

        return (int) $this->options[$name]->getValue();
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
