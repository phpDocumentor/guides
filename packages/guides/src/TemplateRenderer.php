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

interface TemplateRenderer
{
    /** @param array<string, mixed> $params */
    public function renderTemplate(RenderContext $context, string $template, array $params = []): string;

    public function isTemplateFound(RenderContext $context, string $template): bool;
}
