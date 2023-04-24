<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

class HtmlRenderer extends BaseTypeRenderer
{
    public const TYPE = 'html';

    public function supports(string $outputFormat): bool
    {
        return $outputFormat === self::TYPE;
    }
}
