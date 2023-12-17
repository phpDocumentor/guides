<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Code\DependencyInjection;

use Highlight\Highlighter as HighlightPHP;
use phpDocumentor\Guides\Code\Highlighter\HighlightPhpHighlighter;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function assert;
use function dirname;

class CodeExtension extends Extension implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('code');
        $rootNode = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->fixXmlConfig('alias', 'aliases')
            ->fixXmlConfig('language')
            ->children()
                ->arrayNode('aliases')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('languages')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 3) . '/resources/config'),
        );

        $loader->load('guides-code.php');

        $container->getDefinition(HighlightPhpHighlighter::class)
            ->replaceArgument('$languageAliases', $config['aliases'] ?? []);

        $highlighter = $container->getDefinition(HighlightPHP::class);
        foreach ($config['languages'] ?? [] as $name => $definitionFilePath) {
            $highlighter->addMethodCall('registerLanguage', [$name, $definitionFilePath, true]);
        }
    }

    /** @param mixed[] $config */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }
}
