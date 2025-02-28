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

use function sprintf;
use function strlen;
use function substr;
use function trim;

final class FileCollector
{
    /** @var string[][] */
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
     * @param SpecificationInterface|Exclude|null $excludedSpecification specification that is used to exclude specific files/directories
     */
    public function collect(FilesystemInterface|FileSystem $filesystem, string $directory, string $extension, SpecificationInterface|Exclude|null $excludedSpecification = null): Files
    {
        $directory = trim($directory, '/');
        $specification = $this->getSpecification($excludedSpecification, $directory, $extension);

        /** @var array<array<string>> $files */
        $files = $filesystem->find($specification);

        // completely populate the splFileInfos property
        $this->fileInfos = [];
        foreach ($files as $fileInfo) {
            $dirname = $fileInfo['dirname'];

            if (strlen($directory) > 0) {
                // Make paths relative to the provided source folder
                $dirname = substr($fileInfo['dirname'], strlen($directory) + 1) ?: '';
            }

            $documentPath = $this->getFilenameFromFile($fileInfo['filename'], $dirname);

            $this->fileInfos[$documentPath] = $fileInfo;
        }

        $parseQueue = new Files();
        foreach ($this->fileInfos as $filename => $_fileInfo) {
            if (!$this->doesFileRequireParsing((string) $filename)) {
                continue;
            }

            $parseQueue->add((string) $filename);
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

    private function getSpecification(Exclude|SpecificationInterface|null $excludedSpecification, string $directory, string $extension): SpecificationInterface
    {
        if ($excludedSpecification instanceof Exclude) {
            if ($directory === '') {
                $directory = new Path('./');
            }

            return $this->specificationFactory->create([$directory], $excludedSpecification, [$extension]);
        }

        $specification = new AndSpecification(new InPath(new \Flyfinder\Path($directory)), new HasExtension([$extension]));
        if ($excludedSpecification) {
            $specification = new AndSpecification($specification, new NotSpecification($excludedSpecification));
        }

        return $specification;
    }
}
