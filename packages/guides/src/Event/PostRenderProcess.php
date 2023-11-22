<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Handlers\RenderCommand;

final class PostRenderProcess
{
    public function __construct(private readonly RenderCommand $command)
    {
    }

    public function getCommand(): RenderCommand
    {
        return $this->command;
    }
}
