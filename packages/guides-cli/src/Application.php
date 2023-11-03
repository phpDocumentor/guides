<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

use function count;
use function getcwd;

final class Application extends BaseApplication
{
    /** @param iterable<Command> $commands */
    public function __construct(iterable $commands, string $defaultCommand = 'run')
    {
        parent::__construct('phpDocumentor guides', '1.0.0');

        foreach ($commands as $command) {
            $this->add($command);
        }

        if (count($commands) !== 1) {
            return;
        }

        $this->setDefaultCommand($defaultCommand, true);
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'The path to a "guides.xml" config file, if needed',
            getcwd(),
        ));

        return $definition;
    }
}
