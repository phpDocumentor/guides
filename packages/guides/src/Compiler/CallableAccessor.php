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

namespace phpDocumentor\Guides\Compiler;

use Closure;
use LogicException;
use phpDocumentor\Guides\Nodes\Node;

use function array_key_exists;
use function assert;
use function is_array;
use function sprintf;

/**
 * @template TOwner of Node
 * @template TChild
 */
class CallableAccessor implements SubNodeAccessor
{
    /** @var Closure(TOwner): TChild */
    private readonly Closure $get;

    /** @var Closure(TOwner, TChild): TOwner */
    private readonly Closure $set;

    /**
     * @param callable(TOwner): TChild $get
     * @param callable(TOwner, TChild): TOwner $set
     */
    public function __construct(
        public readonly string $name,
        callable $get,
        callable $set,
        private readonly bool $nullable = false,
    ) {
        $this->get = Closure::fromCallable($get);
        $this->set = Closure::fromCallable($set);
    }

    private function get(Node $node): mixed
    {
        /** @var TOwner $node */
        return ($this->get)($node);
    }

    private function set(Node $node, mixed $value): Node
    {
        /**
         * @var TOwner $node
         * @var TChild $value
         */
        $result = ($this->set)($node, $value);
        assert($result instanceof Node);

        return $result;
    }

    /** @return array<Node> */
    public function items(Node $owner): array
    {
        $value = $this->get($owner);
        if ($value instanceof Node) {
            return [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $key => $item) {
            if (!($item instanceof Node)) {
                continue;
            }

            $items[$key] = $item;
        }

        return $items;
    }

    public function replaceAt(Node $owner, int|string|null $key, Node $node): Node
    {
        $value = $this->get($owner);
        if (!is_array($value)) {
            return $this->set($owner, $node);
        }

        $this->assertKey($value, $key);
        $value[$key] = $node;

        return $this->set($owner, $value);
    }

    public function removeAt(Node $owner, int|string|null $key): Node
    {
        $value = $this->get($owner);
        if (!is_array($value)) {
            if (!$this->nullable) {
                throw new LogicException(sprintf('Field "%s" is not nullable, its node cannot be removed', $this->name));
            }

            return $this->set($owner, null);
        }

        $this->assertKey($value, $key);
        unset($value[$key]);

        return $this->set($owner, $value);
    }

    /**
     * @param array<array-key, mixed> $value
     *
     * @phpstan-assert array-key $key
     */
    private function assertKey(array $value, int|string|null $key): void
    {
        if ($key === null || !array_key_exists($key, $value)) {
            throw new LogicException(sprintf('Field "%s" has no item at the given key', $this->name));
        }
    }
}
