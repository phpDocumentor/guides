<?php

declare(strict_types=1);

/**
 * Maps AST Node classes to Twig template paths for the "page" output format.
 *
 * All paths are relative to this package's `resources/template/page/` directory,
 * which is registered as a Twig filesystem path by
 * {@see \phpDocumentor\Guides\Pages\DependencyInjection\PagesExtension::prepend()}.
 *
 * Node types not listed here fall back to the core HTML templates (the `base_template_paths`
 * precedence order means this package's templates are checked first).
 */

use phpDocumentor\Guides\Pages\Nodes\PageNode;

return [PageNode::class => 'structure/page.html.twig'];
