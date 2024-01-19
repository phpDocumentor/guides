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

use function array_unique;
use function array_walk;
use function implode;
use function preg_replace;
use function strtolower;

/** @template TValue */
abstract class AbstractNode implements Node
{
    /** @var string[] */
    protected array $classes = [];

    /** @var array<string, scalar|null> */
    protected array $options = [];

    /** @var TValue */
    protected $value;

    /** @return array<string, scalar|null> */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** @param TValue $value */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /** @return TValue */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /** @return string[] */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Normalizes class names following the rules of identifier-normalization
     *
     * @see https://docutils.sourceforge.io/docs/ref/rst/directives.html#identifier-normalization
     *
     * @param string[] $classes
     */
    public function setClasses(array $classes): void
    {
        array_walk($classes, static function (&$value): void {
            // alphabetic characters to lowercase,
            $value = strtolower($value);
            // TODO: accented characters to the base character
            // non-alphanumeric characters to hyphens
            $value = (string) preg_replace('/[^a-z0-9]+/', '-', $value);
            // consecutive hyphens into one hyphen
            $value = (string) preg_replace('/-+/', '-', $value);
            // strip leading hyphens and number characters
            $value = (string) preg_replace('/^[0-9\-]+/', '', $value);
            // strip trailing hyphens
            $value = (string) preg_replace('/-$/', '', $value);
        });
        $this->classes = array_unique($classes);
    }

    public function getClassesString(): string
    {
        return implode(' ', $this->classes);
    }

    /**
     * @param array<string, scalar|null> $options
     *
     * @return static
     */
    public function withOptions(array $options): Node
    {
        $result = clone $this;
        $result->options = [...$result->options, ...$options];

        return $result;
    }

    /**
     * Adds $options as default options without overriding any options already set.
     *
     * @param array<string, scalar|null> $options
     *
     * @return static
     */
    public function withKeepExistingOptions(array $options): Node
    {
        $result = clone $this;
        $result->options = [...$options, ...$result->options];

        return $result;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * @param TType|null $default
     *
     * @phpstan-return ($default is null ? mixed|null: TType|null)
     *
     * @template TType as mixed
     */
    public function getOption(string $name, $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }
}
