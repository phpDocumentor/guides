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

namespace phpDocumentor\Guides\Cli\Internal;

use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Settings\ProjectSettings;
use Symfony\Component\Console\Input\InputInterface;

final class RunCommand
{
    public function __construct(
        public readonly ProjectSettings $settings,
        public readonly ProjectNode $projectNode,
        public readonly InputInterface|null $input,
    ) {
    }
}
