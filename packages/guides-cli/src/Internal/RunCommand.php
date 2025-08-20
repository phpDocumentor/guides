<?php

declare(strict_types=1);

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
    )
    {
    }
}
