<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use phpDocumentor\Guides\Configuration;
use phpDocumentor\Guides\DependencyInjection\Compiler\NodeRendererPass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Twig\Loader\FilesystemLoader;

use function dirname;

class GuidesExtension extends Extension
{
    public function configure(ArrayNodeDefinition $root): void
    {
        $root
            ->fixXmlConfig('template_path')
            ->children()
                ->arrayNode('template_paths')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
            ->end()
        ;
    }

    /** @param string[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 2) . '/resources/config'),
        );

        $loader->load('command_bus.php');
        $loader->load('guides.php');

        $filesystemLoader = $container->getDefinition(FilesystemLoader::class);
        foreach ($config['template_paths'] as $path) {
            $filesystemLoader->addMethodCall('prependPath', [$path]);
        }
    }

    public function process(ContainerBuilder $container): void
    {
        (new NodeRendererPass())->process($container);
    }
}
