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

use function dirname;
use function ltrim;

class ParserContext
{
    public function __construct(
        private readonly ProjectNode $projectNode,
        private readonly string $currentFileName,
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
        if (BaseUri::from($url)->isAbsolutePath()) {
            return $this->currentDirectory . '/' . ltrim($url, '/');
        }

        return $this->currentDirectory . '/' . $this->getDirName() . '/' . ltrim($url, '/');
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
