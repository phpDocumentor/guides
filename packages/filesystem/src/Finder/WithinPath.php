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

namespace phpDocumentor\FileSystem\Finder;

use Flyfinder\Specification\CompositeSpecification;
use League\Flysystem\StorageAttributes;

use function is_string;
use function str_starts_with;
use function trim;

/**
 * Matches a path and everything below it, so a directory is matched with all its contents.
 *
 * Unlike a {@see \Flyfinder\Specification\Glob}, it lets the finder skip a matching directory as a whole, and it does
 * not change the values it is given, which Flysystem 3 does not allow.
 */
final class WithinPath extends CompositeSpecification
{
    private readonly string $path;

    public function __construct(string $path)
    {
        $this->path = trim($path, '/');
    }

    /** @param array<string, mixed>|StorageAttributes $value */
    public function isSatisfiedBy(array|StorageAttributes $value): bool
    {
        $path = $this->pathOf($value);

        return $path === $this->path || str_starts_with($path, $this->path . '/');
    }

    /** @param array<string, mixed>|StorageAttributes $value */
    public function canBeSatisfiedBySomethingBelow(array|StorageAttributes $value): bool
    {
        $path = $this->pathOf($value);

        return $path === '' || $this->isSatisfiedBy($value) || str_starts_with($this->path, $path . '/');
    }

    /** @param array<string, mixed>|StorageAttributes $value */
    public function willBeSatisfiedByEverythingBelow(array|StorageAttributes $value): bool
    {
        return $this->isSatisfiedBy($value);
    }

    /** @param array<string, mixed>|StorageAttributes $value */
    private function pathOf(array|StorageAttributes $value): string
    {
        /** @psalm-suppress ImpureMethodCall */
        $path = $value['path'] ?? '';

        return is_string($path) ? trim($path, '/') : '';
    }
}
