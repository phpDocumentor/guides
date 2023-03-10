<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Console\Command;

use phpDocumentor\Guides\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Run extends Command
{
    private $container;

    public function __construct(Parser $container)
    {
        parent::__construct('run');

        $this->container = $container;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}
