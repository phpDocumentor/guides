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

namespace phpDocumentor\Guides\Code\Twig;

use phpDocumentor\Guides\Code\Highlighter\Highlighter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function is_array;

final class CodeExtension extends AbstractExtension
{
    public function __construct(
        private Highlighter $highlighter,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('highlight', $this->highlight(...), ['is_safe' => ['html'], 'needs_context' => true]),
        ];
    }

    /**
     * `CodeNode::getLanguage()` is nullable, and templates pass it straight into this filter, so null has to be
     * accepted here and mean the same as an omitted language.
     *
     * @param array<string, mixed> $context
     */
    public function highlight(array $context, string $code, string|null $language = null): string
    {
        $debugInformation = $context['debugInformation'] ?? [];
        if (!is_array($debugInformation)) {
            $debugInformation = [];
        }

        return ($this->highlighter)($language ?? 'text', $code, $debugInformation)->code;
    }
}
