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

use function array_keys;
use function array_map;
use function count;
use function get_debug_type;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function preg_match;
use function sprintf;
use function strlen;

/**
 * Represents the exported symbols (anchors, titles, citations) from a single document.
 *
 * Used for incremental rendering to detect when a document's "public interface" changes.
 *
 * Hash Format Requirements:
 * - contentHash and exportsHash must be hexadecimal strings (0-9, a-f, A-F) or empty
 * - This is validated in fromArray() but NOT in the constructor for performance
 * - Callers creating DocumentExports directly are responsible for providing valid hashes
 * - Invalid hash formats will cause fromArray() to throw InvalidArgumentException on reload
 *
 * Document Path:
 * - documentPath can be empty string (defaults to '' when missing in fromArray())
 * - Empty paths are allowed for test/fallback scenarios but in production use,
 *   documentPath should always be set to the actual source file path
 */
final class DocumentExports
{
    /** Maximum allowed length for string fields to prevent memory exhaustion attacks */
    private const MAX_STRING_LENGTH = 65_536;

    /** Maximum allowed number of items in array fields to prevent memory exhaustion */
    private const MAX_ARRAY_ITEMS = 10_000;

    /** Maximum allowed timestamp value (year 3000 in Unix time) to catch corrupted data */
    private const MAX_TIMESTAMP = 32_503_680_000;

    /**
     * @param string $documentPath Source file path (relative)
     * @param string $contentHash Hash of the source file content
     * @param string $exportsHash Hash of exports only (for dependency invalidation)
     * @param array<string, string> $anchors Anchor name => title mapping
     * @param array<string, string> $sectionTitles Section ID => title mapping
     * @param string[] $citations Citation names defined in this document
     * @param int $lastModified Unix timestamp of last modification
     * @param string $documentTitle Document title (first heading)
     */
    public function __construct(
        public readonly string $documentPath,
        public readonly string $contentHash,
        public readonly string $exportsHash,
        public readonly array $anchors,
        public readonly array $sectionTitles,
        public readonly array $citations,
        public readonly int $lastModified,
        public readonly string $documentTitle = '',
    ) {
    }

    /**
     * Check if the exports (public interface) changed compared to another version.
     * Content can change without exports changing (e.g., fixing a typo in body text).
     */
    public function hasExportsChanged(self $other): bool
    {
        return $this->exportsHash !== $other->exportsHash;
    }

    /**
     * Check if any content changed.
     */
    public function hasContentChanged(self $other): bool
    {
        return $this->contentHash !== $other->contentHash;
    }

    /**
     * Get all anchor names exported by this document.
     *
     * Note: Uses array_map to ensure string return type since PHP converts
     * numeric string keys to integers in arrays.
     *
     * @return string[]
     */
    public function getAnchorNames(): array
    {
        return array_map('strval', array_keys($this->anchors));
    }

    /**
     * Serialize to array for JSON persistence.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'documentPath' => $this->documentPath,
            'contentHash' => $this->contentHash,
            'exportsHash' => $this->exportsHash,
            'anchors' => $this->anchors,
            'sectionTitles' => $this->sectionTitles,
            'citations' => $this->citations,
            'lastModified' => $this->lastModified,
            'documentTitle' => $this->documentTitle,
        ];
    }

    /**
     * Deserialize from array.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException If data format is invalid
     */
    public static function fromArray(array $data): self
    {
        $anchors = self::validateStringMap($data, 'anchors');
        $sectionTitles = self::validateStringMap($data, 'sectionTitles');
        $citations = self::validateStringList($data, 'citations');

        $documentPath = self::validateDocumentPath($data);
        $contentHash = self::validateHash($data, 'contentHash');
        $exportsHash = self::validateHash($data, 'exportsHash');
        $documentTitle = self::validateString($data, 'documentTitle');

        $lastModified = $data['lastModified'] ?? 0;
        if (!is_int($lastModified) || $lastModified < 0 || $lastModified > self::MAX_TIMESTAMP) {
            throw new InvalidArgumentException(sprintf(
                'DocumentExports: expected lastModified to be int between 0 and %d, got %s',
                self::MAX_TIMESTAMP,
                is_int($lastModified) ? (string) $lastModified : get_debug_type($lastModified),
            ));
        }

        return new self(
            documentPath: $documentPath,
            contentHash: $contentHash,
            exportsHash: $exportsHash,
            anchors: $anchors,
            sectionTitles: $sectionTitles,
            citations: $citations,
            lastModified: $lastModified,
            documentTitle: $documentTitle,
        );
    }

    /**
     * Validate and extract a string field with length checking.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException If value is not a string or exceeds max length
     */
    private static function validateString(array $data, string $field): string
    {
        $value = $data[$field] ?? '';

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'DocumentExports: expected %s to be string, got %s',
                $field,
                get_debug_type($value),
            ));
        }

        if (strlen($value) > self::MAX_STRING_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'DocumentExports: %s exceeds maximum length of %d bytes',
                $field,
                self::MAX_STRING_LENGTH,
            ));
        }

        return $value;
    }

    /**
     * Validate and extract a document path with additional safety checks.
     *
     * Rejects control characters (including null bytes) that could cause issues
     * in filesystem operations, log output, or other string processing.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException If path is invalid
     */
    private static function validateDocumentPath(array $data): string
    {
        $value = self::validateString($data, 'documentPath');

        // Allow empty path (documented as valid for test/fallback scenarios)
        if ($value === '') {
            return '';
        }

        // Reject control characters (0x00-0x1F and 0x7F) that could cause issues
        // in filesystem operations, log output, or terminal display
        if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            throw new InvalidArgumentException(
                'DocumentExports: documentPath contains invalid control characters',
            );
        }

        return $value;
    }

    /** Valid hash lengths: xxh128 (32 hex chars) or sha256 (64 hex chars) */
    private const VALID_HASH_LENGTHS = [32, 64];

    /**
     * Validate and extract a hash field (hexadecimal string or empty).
     *
     * Accepts:
     * - Empty string (for new documents or when hash wasn't computed)
     * - 32 hex chars (xxh128 algorithm)
     * - 64 hex chars (sha256 algorithm)
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException If value is not a valid hash format or length
     */
    private static function validateHash(array $data, string $field): string
    {
        $value = self::validateString($data, $field);

        // Allow empty hash (for new documents or when hash wasn't computed)
        if ($value === '') {
            return '';
        }

        // Validate hexadecimal format
        if (preg_match('/^[a-f0-9]+$/i', $value) !== 1) {
            throw new InvalidArgumentException(sprintf(
                'DocumentExports: %s must be a hexadecimal string, got invalid format',
                $field,
            ));
        }

        // Validate hash length matches known algorithms
        $length = strlen($value);
        if (!in_array($length, self::VALID_HASH_LENGTHS, true)) {
            throw new InvalidArgumentException(sprintf(
                'DocumentExports: %s must be 32 (xxh128) or 64 (sha256) hex chars, got %d',
                $field,
                $length,
            ));
        }

        return $value;
    }

    /**
     * Validate a string-to-string map array field.
     *
     * Note on key collision: PHP arrays can have both integer key 123 and string key "123"
     * which would collide when cast to string. However, JSON-decoded arrays cannot have
     * both forms of the same key, so this is not a concern in practice. The later value
     * would overwrite the earlier one if such a collision occurred.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, string>
     *
     * @throws InvalidArgumentException If validation fails
     */
    private static function validateStringMap(array $data, string $field): array
    {
        $value = $data[$field] ?? [];

        if (!is_array($value)) {
            throw new InvalidArgumentException(sprintf(
                'DocumentExports: expected %s to be array, got %s',
                $field,
                get_debug_type($value),
            ));
        }

        if (count($value) > self::MAX_ARRAY_ITEMS) {
            throw new InvalidArgumentException(sprintf(
                'DocumentExports: %s exceeds maximum of %d items',
                $field,
                self::MAX_ARRAY_ITEMS,
            ));
        }

        $result = [];
        foreach ($value as $key => $item) {
            $stringKey = (string) $key;

            if (strlen($stringKey) > self::MAX_STRING_LENGTH) {
                throw new InvalidArgumentException(sprintf(
                    'DocumentExports: key in %s exceeds maximum length',
                    $field,
                ));
            }

            if (!is_string($item)) {
                throw new InvalidArgumentException(sprintf(
                    'DocumentExports: expected %s value to be string, got %s',
                    $field,
                    get_debug_type($item),
                ));
            }

            if (strlen($item) > self::MAX_STRING_LENGTH) {
                throw new InvalidArgumentException(sprintf(
                    'DocumentExports: value in %s exceeds maximum length',
                    $field,
                ));
            }

            $result[$stringKey] = $item;
        }

        return $result;
    }

    /**
     * Validate a string list array field.
     *
     * @param array<string, mixed> $data
     *
     * @return string[]
     *
     * @throws InvalidArgumentException If validation fails
     */
    private static function validateStringList(array $data, string $field): array
    {
        $value = $data[$field] ?? [];

        if (!is_array($value)) {
            throw new InvalidArgumentException(sprintf(
                'DocumentExports: expected %s to be array, got %s',
                $field,
                get_debug_type($value),
            ));
        }

        if (count($value) > self::MAX_ARRAY_ITEMS) {
            throw new InvalidArgumentException(sprintf(
                'DocumentExports: %s exceeds maximum of %d items',
                $field,
                self::MAX_ARRAY_ITEMS,
            ));
        }

        $result = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                throw new InvalidArgumentException(sprintf(
                    'DocumentExports: expected %s item to be string, got %s',
                    $field,
                    get_debug_type($item),
                ));
            }

            if (strlen($item) > self::MAX_STRING_LENGTH) {
                throw new InvalidArgumentException(sprintf(
                    'DocumentExports: item in %s exceeds maximum length',
                    $field,
                ));
            }

            $result[] = $item;
        }

        return $result;
    }
}
