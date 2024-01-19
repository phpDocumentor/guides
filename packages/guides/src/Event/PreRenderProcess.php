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

namespace phpDocumentor\Guides\Event;

use phpDocumentor\Guides\Handlers\RenderCommand;

/**
 * This event is called once before each rendering method after all documents have been parsed and
 * all compiler passes (including node transformers have been called.)
 *
 * It can be used to exit the rendering process before anything was rendered. A third party extension could then
 * take over the rendering with its own means.
 */
final class PreRenderProcess
{
    private bool $exitRendering = false;

    public function __construct(
        private readonly RenderCommand $command,
        private readonly int $steps = 1,
    ) {
    }

    public function getCommand(): RenderCommand
    {
        return $this->command;
    }

    public function isExitRendering(): bool
    {
        return $this->exitRendering;
    }

    public function setExitRendering(bool $exitRendering): PreRenderProcess
    {
        $this->exitRendering = $exitRendering;

        return $this;
    }

    public function getSteps(): int
    {
        return $this->steps;
    }
}
