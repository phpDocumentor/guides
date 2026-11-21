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

namespace phpDocumentor\Guides;

use Doctrine\Deprecations\Deprecation;
use Flyfinder\Specification\AndSpecification;
use Flyfinder\Specification\HasExtension;
use Flyfinder\Specification\InPath;
use Flyfinder\Specification\NotSpecification;
use Flyfinder\Specification\SpecificationInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemInterface;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\Finder\Exclude;
use phpDocumentor\FileSystem\Finder\SpecificationFactory;
use phpDocumentor\FileSystem\Finder\SpecificationFactoryInterface;
use phpDocumentor\FileSystem\Path;
use phpDocumentor\FileSystem\StorageAttributes;
use RuntimeException;

use function pathinfo;
use function sprintf;
use function strlen;
use function substr;
use function trim;

use const PATHINFO_EXTENSION;

final class FileCollector
{
    /** @var array<string, StorageAttributes> */
    private array $fileInfos = [];
    private SpecificationFactoryInterface $specificationFactory;

    public function __construct(SpecificationFactoryInterface|null $specificationFactory = null)
    {
        $this->specificationFactory = $specificationFactory ?? new SpecificationFactory();
    }

    /**
     * Scans a directory recursively looking for all files to parse.
     *
     * This takes into account the presence of cached & fresh MetaEntry
     * objects, and avoids adding files to the parse queue that have
     * not changed and whose direct dependencies have not changed.
     *
     * @param string|list<string> $extension
     * @param SpecificationInterface|Exclude|null $excludedSpecification specification that is used to exclude specific files/directories.
     *                                                                   Passing a {@see SpecificationInterface} is deprecated, use {@see Exclude} instead.
     *
     * @throws RuntimeException when two files with different extensions name the same document, such as "page.rst"
     *                          and "page.md".
     */
    public function collect(FilesystemInterface|FileSystem $filesystem, string $directory, string|array $extension, SpecificationInterface|Exclude|null $excludedSpecification = null): Files
    {
        if ($excludedSpecification instanceof SpecificationInterface) {
            Deprecation::triggerIfCalledFromOutside(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1209',
                'Passing %s to %s::collect() is deprecated, use %s instead.',
                $excludedSpecification::class,
                self::class,
                Exclude::class,
            );
        }

        $extensions = (array) $extension;
        $directory = trim($directory, '/');
        $specification = $this->getSpecification($excludedSpecification, $directory, $extensions);

        /** @var array<StorageAttributes> $files */
        $files = $filesystem->find($specification);

        // completely populate the splFileInfos property
        $this->fileInfos = [];
        $fileExtensions = [];
        foreach ($files as $fileInfo) {
            $dirname = $fileInfo['dirname'];

            if (strlen($directory) > 0) {
                // Make paths relative to the provided source folder
                $dirname = substr($fileInfo['dirname'], strlen($directory) + 1) ?: '';
            }

            $documentPath = $this->getFilenameFromFile($fileInfo['filename'], $dirname);
            $fileExtension = pathinfo($fileInfo['basename'], PATHINFO_EXTENSION);

            if (isset($fileExtensions[$documentPath]) && $fileExtensions[$documentPath] !== $fileExtension) {
                throw new RuntimeException(sprintf(
                    'The files "%1$s.%2$s" and "%1$s.%3$s" are both the document "%1$s". Rename or exclude one of them.',
                    $documentPath,
                    $fileExtensions[$documentPath],
                    $fileExtension,
                ));
            }

            $this->fileInfos[$documentPath] = $fileInfo;
            $fileExtensions[$documentPath] = $fileExtension;
        }

        $parseQueue = new Files();
        foreach ($this->fileInfos as $filename => $_fileInfo) {
            if (!$this->doesFileRequireParsing((string) $filename)) {
                continue;
            }

            $parseQueue->add((string) $filename, $fileExtensions[$filename]);
        }

        return $parseQueue;
    }

    private function doesFileRequireParsing(string $filename): bool
    {
        if (!isset($this->fileInfos[$filename])) {
            throw new InvalidArgumentException(
                sprintf('No file info found for "%s" - file does not exist.', $filename),
            );
        }

        return true;
    }

    /**
     * Converts foo/bar.rst to foo/bar (the document filename)
     */
    private function getFilenameFromFile(string $filename, string $dirname): string
    {
        $directory = $dirname ? $dirname . '/' : '';

        return $directory . $filename;
    }

    /** @param list<string> $extensions */
    private function getSpecification(Exclude|SpecificationInterface|null $excludedSpecification, string $directory, array $extensions): SpecificationInterface
    {
        if ($excludedSpecification instanceof Exclude) {
            if ($directory === '') {
                $directory = new Path('./');
            }

            return $this->specificationFactory->create([$directory], $excludedSpecification, $extensions);
        }

        $specification = new AndSpecification(new InPath(new \Flyfinder\Path($directory)), new HasExtension($extensions));
        if ($excludedSpecification) {
            $specification = new AndSpecification($specification, new NotSpecification($excludedSpecification));
        }

        return $specification;
    }
}
