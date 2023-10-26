<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

use Symfony\Component\String\Slugger\AsciiSlugger;

use function strtolower;

class SluggerAnchorReducer implements AnchorReducer
{
    public function reduceAnchor(string $rawAnchor): string
    {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($rawAnchor);

        return strtolower($slug->toString());
    }
}
