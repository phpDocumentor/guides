<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Code\Twig;

use phpDocumentor\Guides\Code\Highlighter\Highlighter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CodeExtension extends AbstractExtension
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
            new TwigFilter('highlight', $this->highlight(...), ['is_safe' => ['html']]),
        ];
    }

    public function highlight(string $code, string $language = 'text'): string
    {
        return ($this->highlighter)($language, $code)->code;
    }
}
