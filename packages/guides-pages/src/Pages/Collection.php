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

namespace phpDocumentor\Guides\Pages;

use function ltrim;

/**
 * Value object representing a single content-type collection as configured in
 * `guides.xml`.
 *
 * A collection groups a set of source files (located in {@see getSourceDirectory()})
 * into a list of {@see \phpDocumentor\Guides\Pages\Nodes\ContentTypeItemNode}
 * instances, and generates a single overview page at {@see getOverviewPath()}.
 *
 * Leading slashes are normalised away from both path fields on construction so
 * that callers never need to call `ltrim(…, '/')` themselves.
 *
 * Instances are created by {@see fromArray()} from the raw Symfony Config array
 * produced by {@see \phpDocumentor\Guides\Pages\DependencyInjection\PagesExtension}.
 */
final class Collection
{
    private function __construct(
        private readonly string $sourceDirectory,
        private readonly string $overviewPath,
        private readonly string $overviewTitle,
        private readonly string $overviewTemplate,
        private readonly string $itemTemplate,
    ) {
    }

    /** @param array{source_directory: string, overview_path: string, overview_title: string, overview_template: string, item_template: string} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            ltrim($data['source_directory'], '/'),
            ltrim($data['overview_path'], '/'),
            $data['overview_title'],
            $data['overview_template'],
            $data['item_template'],
        );
    }

    /** Path (relative to the docs source root) containing the collection's source files. */
    public function getSourceDirectory(): string
    {
        return $this->sourceDirectory;
    }

    /** Output path for the generated overview page, without the `.html` extension. */
    public function getOverviewPath(): string
    {
        return $this->overviewPath;
    }

    /** Title displayed on the overview page. */
    public function getOverviewTitle(): string
    {
        return $this->overviewTitle;
    }

    /** Twig template used to render the overview page. */
    public function getOverviewTemplate(): string
    {
        return $this->overviewTemplate;
    }

    /** Default Twig template for individual items; may be overridden per item via `:page-template:`. */
    public function getItemTemplate(): string
    {
        return $this->itemTemplate;
    }
}
