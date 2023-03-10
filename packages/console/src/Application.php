<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

final class Application extends BaseApplication
{
    public function __construct(iterable $commands, string $defaultCommand = 'run')
    {
        parent::__construct('phpDocumentor guides', '1.0.0');

        foreach ($commands as $command) {
            $this->add($command);
        }

        $this->setDefaultCommand($defaultCommand, true);
    }
}
