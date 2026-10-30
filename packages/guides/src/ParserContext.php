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

use League\Flysystem\FilesystemInterface;
use League\Uri\BaseUri;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use RuntimeException;

use function array_key_last;
use function array_pop;
use function count;
use function dirname;
use function in_array;
use function ltrim;
use function str_starts_with;

class ParserContext
{
    /** @var string[] */
    private array $activeIncludeChain = [];

    public function __construct(
        private readonly ProjectNode $projectNode,
        private string $currentFileName,
        private readonly string $currentDirectory,
        private readonly int $initialHeaderLevel,
        private readonly FilesystemInterface|FileSystem $origin,
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }

    public function getInitialHeaderLevel(): int
    {
        return $this->initialHeaderLevel;
    }

    public function absoluteRelativePath(string $url): string
    {
        return $this->currentDirectory . '/' . $this->relativeFileName($url);
    }

    public function getDirName(): string
    {
        $dirname = dirname($this->currentFileName);

        if ($dirname === '.') {
            return '';
        }

        return $dirname;
    }

    public function getCurrentFileName(): string
    {
        return $this->currentFileName;
    }

    public function setCurrentFileName(string $currentFileName): void
    {
        $this->currentFileName = $currentFileName;
    }

    /** @return string[] */
    public function getActiveIncludeChain(): array
    {
        return $this->activeIncludeChain === []
            ? [$this->currentFileName]
            : $this->activeIncludeChain;
    }

    public function isIncludeActive(string $path): bool
    {
        $normalizedPath = ltrim($path, '/');
        if (in_array($path, $this->activeIncludeChain, true)) {
            return true;
        }

        if ($this->activeIncludeChain !== []) {
            return false;
        }

        return in_array($normalizedPath, [$this->currentFileName, $this->currentFileName . '.rst'], true);
    }

    public function enterInclude(string $path): void
    {
        $this->activeIncludeChain = $this->activeIncludeChain === []
            ? [$this->currentFileName, $path]
            : [...$this->activeIncludeChain, $path];
        $this->currentFileName = $path;
    }

    public function leaveInclude(): void
    {
        array_pop($this->activeIncludeChain);
        if ($this->activeIncludeChain === []) {
            return;
        }

        $this->currentFileName = $this->activeIncludeChain[array_key_last($this->activeIncludeChain)];
        if (count($this->activeIncludeChain) !== 1) {
            return;
        }

        $this->activeIncludeChain = [];
    }

    public function relativeFileName(string $url): string
    {
        if (BaseUri::from($url)->isAbsolutePath()) {
            return $this->ensureWithinOrigin(
                $this->documentNameResolver->canonicalUrl('', ltrim($url, '/')),
            );
        }

        return $this->ensureWithinOrigin(
            $this->documentNameResolver->canonicalUrl($this->getDirName(), $url),
        );
    }

    private function ensureWithinOrigin(string $relativeFileName): string
    {
        if ($relativeFileName === '..' || str_starts_with($relativeFileName, '../')) {
            throw new RuntimeException('Path resolves outside the configured origin.');
        }

        return $relativeFileName;
    }

    /** @return array<string, string> */
    public function getLoggerInformation(): array
    {
        return [
            'rst-file' => $this->currentFileName,
        ];
    }

    public function getOrigin(): FilesystemInterface|FileSystem
    {
        return $this->origin;
    }

    public function getCurrentDirectory(): string
    {
        return $this->currentDirectory;
    }

    public function getUrl(): string
    {
        return $this->currentFileName;
    }

    /**
     * Return the current file's absolute path on the Origin file system.
     *
     * In order to load files relative to the current file (such as embedding UML diagrams) the environment
     * must expose what the absolute path relative to the Origin is.
     *
     * @see self::setCurrentAbsolutePath() for more information
     * @see self::getOrigin() for the filesystem on which to use this path
     */
    public function getCurrentAbsolutePath(): string
    {
        return $this->documentNameResolver->absoluteUrl($this->currentDirectory, $this->currentFileName);
    }
}
