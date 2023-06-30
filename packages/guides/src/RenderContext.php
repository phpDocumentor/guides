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
use League\Uri\Uri;
use League\Uri\UriInfo;
use phpDocumentor\Guides\Nodes\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;

use function dirname;
use function ltrim;
use function trim;

class RenderContext
{
    private string $destinationPath;

    private DocumentNode $document;

    private function __construct(
        string $outputFolder,
        private readonly string $currentFileName,
        private readonly FilesystemInterface $origin,
        private readonly FilesystemInterface $destination,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $outputFormat,
        private readonly ProjectNode $projectNode,
        private readonly string $absolutePathPrefix = '/',
    ) {
        $this->destinationPath = trim($outputFolder, '/');
    }

    public static function forDocument(
        DocumentNode $documentNode,
        FilesystemInterface $origin,
        FilesystemInterface $destination,
        string $destinationPath,
        UrlGeneratorInterface $urlGenerator,
        string $ouputFormat,
        ProjectNode $projectNode,
        string $absolutePathPrefix = '/',
    ): self {
        $self = new self(
            $destinationPath,
            $documentNode->getFilePath(),
            $origin,
            $destination,
            $urlGenerator,
            $ouputFormat,
            $projectNode,
            $absolutePathPrefix
        );

        $self->document = $documentNode;

        return $self;
    }

    /**
     * @param TType $default
     *
     * @phpstan-return TType|string|Node
     *
     * @template TType as mixed
     */
    public function getVariable(string $variable, mixed $default = null): mixed
    {
        return $this->document->getVariable($variable, $default);
    }

    public function getLink(string $name, bool $relative = true): string
    {
        $link = $this->document->getLink($name);

        if ($link !== null) {
            if ($relative) {
                return $this->urlGenerator->relativeUrl($link);
            }

            return $link;
        }

        return '';
    }

    public function canonicalUrl(string $url): string|null
    {
        return $this->urlGenerator->canonicalUrl($this->getDirName(), $url);
    }

    public function relativeDocUrl(string $filename, string|null $anchor = null): string
    {
        if (UriInfo::isAbsolutePath(Uri::createFromString($filename))) {
            return $this->destinationPath . $this->urlGenerator->createFileUrl($filename, $this->outputFormat, $anchor);
        }

        $baseUrl = ltrim($this->urlGenerator->absoluteUrl($this->destinationPath, $this->getDirName()), '/');

        if ($this->projectNode->findDocumentEntry($filename) !== null) {
            return $this->destinationPath . '/'
                . $this->urlGenerator->createFileUrl($filename, $this->outputFormat, $anchor);
        }

        return $this->urlGenerator->canonicalUrl(
            $baseUrl,
            $this->urlGenerator->createFileUrl($filename, $this->outputFormat, $anchor),
        );
    }

    private function getDirName(): string
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
            'rst-file' => $this->getCurrentFileName(),
        ];
    }

    public function getOrigin(): FilesystemInterface
    {
        return $this->origin;
    }

    public function getCurrentDocumentEntry(): DocumentEntryNode|null
    {
        return $this->projectNode->findDocumentEntry($this->currentFileName);
    }

    public function getDestinationPath(): string
    {
        return $this->destinationPath;
    }

    public function setDestinationPath(string $path): void
    {
        $this->destinationPath = $path;
    }

    public function getDestination(): FilesystemInterface
    {
        return $this->destination;
    }

    public function getCurrentFileDestination(): string
    {
        return $this->destinationPath . '/' . $this->currentFileName . '.' . $this->outputFormat;
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function getAbsolutePathPrefix(): string
    {
        return $this->absolutePathPrefix;
    }

}
