<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use phpDocumentor\Guides\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Twig\Loader\FilesystemLoader;

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

        foreach ($configs as $configArray) {
            $config = $configArray[0] ?? false;
            if (!($config instanceof Configuration)) {
                continue;
            }

            $container->getDefinition(FilesystemLoader::class)
                ->setArgument('$paths', $config->getTemplatePaths());
        }
    }
}
