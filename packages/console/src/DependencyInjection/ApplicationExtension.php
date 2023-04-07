<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Console\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use function dirname;

class ApplicationExtension extends Extension
{
    /** @param string[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->setAlias(ContainerInterface::class, 'service_container');

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/resources/config'));
        $loader->load('guides-restructured-text.yml');

        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/resources/config'));
        $loader->load('command_bus.php');
        $loader->load('services.php');
    }

    public function getAlias(): string
    {
        return 'application';
    }
}
