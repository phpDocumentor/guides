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

namespace phpDocumentor\Guides\Pages\Renderer;

use phpDocumentor\Guides\Renderer\BaseTypeRenderer;

/**
 * Renders every parsed document as a standalone HTML page (format: "page").
 *
 * Activate this renderer by setting `output-format="page"` (or adding `page` to
 * the `output_format` list) in your `guides.xml`, and by registering
 * {@see \phpDocumentor\Guides\Pages\DependencyInjection\PagesExtension} as an
 * extension in the same file.
 *
 * Because the page format uses the standard node-renderer pipeline inherited from
 * {@see BaseTypeRenderer}, any Node type that has a template registered for the
 * "page" format (via `guides.xml` `<template>` entries or through
 * {@see \phpDocumentor\Guides\Pages\DependencyInjection\PagesExtension::prepend()})
 * will be rendered automatically.
 */
final class PageRenderer extends BaseTypeRenderer
{
}
