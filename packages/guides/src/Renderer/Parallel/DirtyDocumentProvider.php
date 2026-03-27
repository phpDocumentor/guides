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

namespace phpDocumentor\Guides\Renderer\Parallel;

/**
 * Optional interface for incremental rendering integration.
 *
 * Implementations provide information about which documents need re-rendering
 * based on change detection. When provided to ForkingRenderer, unchanged
 * documents can be skipped during rendering.
 *
 * This is typically implemented by incremental build systems that track
 * document dependencies and content changes.
 */
interface DirtyDocumentProvider
{
    /**
     * Check if incremental rendering is enabled.
     *
     * When false, all documents will be rendered.
     * When true, only documents in the dirty set will be rendered.
     */
    public function isIncrementalEnabled(): bool;

    /**
     * Compute the set of document paths that need re-rendering.
     *
     * Returns an array of document paths (relative paths without extension)
     * that have changed or have dependencies that changed.
     *
     * @return string[] Document paths that need rendering
     */
    public function computeDirtySet(): array;
}
