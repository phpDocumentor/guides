<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

class LatexRenderer extends BaseTypeRenderer
{
    final public const TYPE = 'tex';

    public function supports(string $outputFormat): bool
    {
        return $outputFormat === self::TYPE;
    }
}
