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

use phpDocumentor\Guides\Nodes\Node;

final class DummyNode implements Node
{
    /** @param DirectiveOption[] $directiveOptions */
    public function __construct(private readonly string $name, private readonly string $data, private readonly array $directiveOptions)
    {
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

    /** {@inheritDoc} */
    public function withKeepExistingOptions(array $options): Node
    {
        return $this;
    }

    public function hasOption(string $name): bool
    {
        return false;
    }

    public function setValue(mixed $value): void
    {
    }

    public function getValue(): mixed
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
