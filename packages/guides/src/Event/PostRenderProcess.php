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
 * This event is called once after each rendering method after all documents have been rendered.
 *
 * It can for example be used to copy assets into the target directory after rendering.
 */
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
