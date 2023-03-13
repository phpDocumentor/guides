<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Console\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ApplicationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setAlias(ContainerInterface::class, 'service_container');

        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/resources/config'));
        $loader->load('services.yml');

        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/resources/config'));
        $loader->load('command_bus.php');
    }

    public function getAlias(): string
    {
        return 'application';
    }
}
