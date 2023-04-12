<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function dirname;

class GuidesExtension extends Extension
{
    /** @param string[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 2) . '/resources/config')
        );

        $loader->load('command_bus.php');
        $loader->load('guides.php');
    }
}
