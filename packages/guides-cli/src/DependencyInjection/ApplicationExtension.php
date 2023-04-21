<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function dirname;

class ApplicationExtension extends Extension
{
    /** @param string[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->setAlias(ContainerInterface::class, 'service_container');

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 2) . '/resources/config'),
        );

        $loader->load('services.php');
    }

    public function getAlias(): string
    {
        return 'application';
    }
}
