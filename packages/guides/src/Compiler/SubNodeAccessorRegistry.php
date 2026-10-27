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

use phpDocumentor\Guides\Nodes\Node;

final class SubNodeAccessorRegistry
{
    /** @var array<class-string<Node>, SubnodeList> */
    private array $registrations = [];

    /** @param iterable<SubNodeAccessorProvider> $providers */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $provider->register($this);
        }
    }

    /** @param class-string<Node> $nodeClass */
    public function register(string $nodeClass, SubnodeList $fields): void
    {
        $this->registrations[$nodeClass] = isset($this->registrations[$nodeClass])
            ? $this->registrations[$nodeClass]->merge($fields)
            : $fields;
    }

    public function getFields(Node $node): SubnodeList|null
    {
        $result = null;
        foreach ($this->registrations as $nodeClass => $registeredFields) {
            if (!($node instanceof $nodeClass)) {
                continue;
            }

            $result = $result === null ? $registeredFields : $result->merge($registeredFields);
        }

        return $result;
    }
}
