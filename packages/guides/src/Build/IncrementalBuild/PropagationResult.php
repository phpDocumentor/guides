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

namespace phpDocumentor\Guides\Build\IncrementalBuild;

use InvalidArgumentException;

use function array_key_exists;
use function array_values;
use function count;
use function in_array;
use function is_array;
use function is_string;

/**
 * Result of dirty propagation through the dependency graph.
 *
 * This class is immutable and represents the outcome of propagating
 * changes through document dependencies.
 */
final class PropagationResult
{
    /**
     * Maximum number of documents in any list (security limit).
     * Consistent with IncrementalBuildCache::MAX_EXPORTS and DirtyPropagator::MAX_PROPAGATION_VISITS.
     */
    private const MAX_DOCUMENTS = 100_000;

    /**
     * @param string[] $documentsToRender Documents that need to be re-rendered
     * @param string[] $documentsToSkip Documents that can use cached output
     * @param string[] $propagatedFrom Documents that caused additional invalidations
     */
    public function __construct(
        public readonly array $documentsToRender,
        public readonly array $documentsToSkip,
        public readonly array $propagatedFrom = [],
    ) {
    }

    /**
     * Check if a document needs rendering.
     */
    public function needsRendering(string $docPath): bool
    {
        return in_array($docPath, $this->documentsToRender, true);
    }

    /**
     * Get count of documents to render.
     */
    public function getRenderCount(): int
    {
        return count($this->documentsToRender);
    }

    /**
     * Get count of documents to skip.
     */
    public function getSkipCount(): int
    {
        return count($this->documentsToSkip);
    }

    /**
     * Get savings ratio (0.0 - 1.0).
     *
     * Returns the proportion of documents that can be skipped.
     */
    public function getSavingsRatio(): float
    {
        $total = $this->getRenderCount() + $this->getSkipCount();
        if ($total === 0) {
            return 0.0;
        }

        return $this->getSkipCount() / $total;
    }

    /**
     * Serialize to array.
     *
     * @return array{documentsToRender: string[], documentsToSkip: string[], propagatedFrom: string[]}
     */
    public function toArray(): array
    {
        return [
            'documentsToRender' => array_values($this->documentsToRender),
            'documentsToSkip' => array_values($this->documentsToSkip),
            'propagatedFrom' => array_values($this->propagatedFrom),
        ];
    }

    /**
     * Create from serialized array with validation.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $documentsToRender = self::validateStringArray(
            array_key_exists('documentsToRender', $data) ? $data['documentsToRender'] : [],
            'documentsToRender',
        );
        $documentsToSkip = self::validateStringArray(
            array_key_exists('documentsToSkip', $data) ? $data['documentsToSkip'] : [],
            'documentsToSkip',
        );
        $propagatedFrom = self::validateStringArray(
            array_key_exists('propagatedFrom', $data) ? $data['propagatedFrom'] : [],
            'propagatedFrom',
        );

        return new self($documentsToRender, $documentsToSkip, $propagatedFrom);
    }

    /**
     * Validate that a value is an array of strings within size limits.
     *
     * @param mixed $value The value (may be any type including null if key was set to null)
     *
     * @return string[]
     */
    private static function validateStringArray(mixed $value, string $fieldName): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('expected ' . $fieldName . ' to be array');
        }

        if (count($value) > self::MAX_DOCUMENTS) {
            throw new InvalidArgumentException($fieldName . ' exceed maximum of ' . self::MAX_DOCUMENTS);
        }

        $result = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                throw new InvalidArgumentException('expected ' . $fieldName . ' item to be string');
            }

            $result[] = $item;
        }

        return $result;
    }
}
