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

namespace phpDocumentor\Guides\Nodes;

use function implode;
use function is_callable;
use function is_string;
use function strlen;
use function substr;
use function trim;

/** @todo split this class into separate node types */
abstract class Node
{
    /** @var Node|string|null|Node[] */
    protected $value;

    /** @var string[] */
    protected $classes = [];

    /** @var mixed[] */
    private $options;

    /**
     * @param Node|string|null|Node[] $value
     */
    public function __construct($value = null)
    {
        $this->value = $value ?? [];
    }

    /**
     * @return Node|string|null|Node[]
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Node|string|null|Node[] $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    public function getClassesString(): string
    {
        return implode(' ', $this->classes);
    }

    /**
     * @param string[] $classes
     */
    public function setClasses(array $classes): void
    {
        $this->classes = $classes;
    }

    /** @deprecated this should not be used, nodes should always be rendered by a renderer */
    public function getValueString(): string
    {
        if ($this->value === null) {
            return '';
        }

        if ($this->value instanceof self) {
            return $this->value->getValueString();
        }

        return $this->value;
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

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
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

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /** @return Node[] */
    public function getChildren(): array
    {
        if (is_array($this->value)) {
            return $this->value;
        }

        if (is_string($this->value) || $this->value === null) {
            return [];
        }

        return [$this->value];
    }

    public function addChildNode(Node $node): void
    {
        if (is_array($this->value) === false) {
            throw new \BadMethodCallException(
                'Cannot call addChildNode on value that\'s not an array for class.' . self::class
            );
        }

        $this->value[] = $node;
    }

    public function removeNode(int $key): self
    {
        if (is_array($this->value) === false) {
            throw new \BadMethodCallException(
                'Cannot call addChildNode on value that\'s not an array for class.' . self::class
            );
        }

        $result = clone $this;
        unset($result->value[$key]);

        return $result;
    }


    /**
     * @return static
     */
    public function replaceNode(int $key, Node $node): self
    {
        $result = clone $this;

        if (is_array($result->value)) {
            $result->value[$key] = $node;
            return $result;
        }

        $result->value = $node;
        return $result;
    }

    /**
     * @param string[] $lines
     */
    protected function normalizeLines(array $lines): string
    {
        if ($lines !== []) {
            $firstLine = $lines[0];

            $length = strlen($firstLine);
            for ($k = 0; $k < $length; $k++) {
                if (trim($firstLine[$k]) !== '') {
                    break;
                }
            }

            foreach ($lines as &$line) {
                $line = substr($line, $k);
            }
        }

        return implode("\n", $lines);
    }
}
