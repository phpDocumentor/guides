<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Directive
{
    public function __construct(
        public readonly string $name,
        public readonly array $aliases = [],
    ) {

    }
}
