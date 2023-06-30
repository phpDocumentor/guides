<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

interface UrlGeneratorInterface
{
    public function generateUrl(string $path): string;

    /**
     * Returns the absolute path, including prefixing '/'.
     *
     * This method will, by design, return an absolute path including the prefixing slash. The slash will make it clear
     * to the other URL generating methods that this need not be resolved and can stay the same.
     */
    public function absoluteUrl(string $basePath, string $url): string;

    /**
     * Resolves a relative URL.
     */
    public function relativeUrl(string $url): string;

    /**
     * Returns the Path used in the Metas to find this file.
     *
     * The Metas collection, which is used to build the table of contents, uses these canonical paths as a unique
     * identifier to find the metadata for that file. Technically speaking, the canonical URL is the absolute URL
     * without the preceeding slash. But due to the many locations that this method is used; it will do its own
     * resolving.
     *
     * @todo simplify this method into the other methods or vice versa
     */
    public function canonicalUrl(string $basePath, string $url): string;

    /**
     * Create a url with a file ending derived from the output format
     */
    public function createFileUrl(string $filename, string $outputFormat = 'html', string|null $anchor = null): string;

    /**
     * Create a URL relative to the current document
     */
    public function createRelativeFileUrl(RenderContext $renderContext, string $filename, string|null $anchor = null): string;

    /**
     * Create a URL relative to the current document
     */
    public function createAbsoluteFileUrl(RenderContext $renderContext, string $filename, string|null $anchor = null): string;
}
