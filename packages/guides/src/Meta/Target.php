<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

interface Target
{
    public function getUrl(): string;

    public function getTitle(): string|null;
}
