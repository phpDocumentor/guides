<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use phpDocumentor\Guides\DependencyInjection\Compiler\NodeRendererPass;
use phpDocumentor\Guides\DependencyInjection\Compiler\ParserRulesPass;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Twig\Theme\ThemeConfig;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function assert;
use function dirname;
use function is_array;

class GuidesExtension extends Extension implements CompilerPassInterface, ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('guides');
        $rootNode = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->children()
                ->arrayNode('project')
                    ->children()
                        ->scalarNode('title')->end()
                        ->scalarNode('version')->end()
                    ->end()
                ->end()
                ->arrayNode('inventories')
                    ->children()
                        ->arrayNode('inventory')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('id')->end()
                                    ->scalarNode('url')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('theme')->end()
                ->scalarNode('input')->end()
                ->scalarNode('output')->end()
                ->scalarNode('input_format')->end()
                ->arrayNode('output_format')
                    ->defaultValue(['html', 'intersphinx'])
                    ->beforeNormalization()
                    ->ifString()
                    ->then(static function ($value) {
                        return [$value];
                    })
                    ->end()
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('log_path')->end()
                ->scalarNode('fail_on_log')->end()
                ->scalarNode('show_progress')->end()
                ->arrayNode('base_template_paths')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('themes')
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifTrue(static fn ($v) => !is_array($v) || !isset($v['templates']))
                            ->then(static fn (string $v) => ['templates' => (array) $v])
                        ->end()
                        ->children()
                            ->scalarNode('extends')->end()
                            ->arrayNode('templates')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
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
            new FileLocator(dirname(__DIR__, 2) . '/resources/config'),
        );

        $loader->load('command_bus.php');
        $loader->load('guides.php');

        $projectSettings = new ProjectSettings();
        if (isset($config['project'])) {
            if (isset($config['project']['version'])) {
                $projectSettings->setVersion((string) $config['project']['version']);
            }

            $projectSettings->setTitle((string) $config['project']['title']);
        }

        if (isset($config['inventories'])) {
            $projectSettings->setInventories($config['inventories']['inventory']);
        }

        if (isset($config['theme'])) {
            $projectSettings->setTheme((string) $config['theme']);
        }

        if (isset($config['input'])) {
            $projectSettings->setInput((string) $config['input']);
        }

        if (isset($config['output'])) {
            $projectSettings->setOutput((string) $config['output']);
        }

        if (isset($config['input_format'])) {
            $projectSettings->setInputFormat((string) $config['input_format']);
        }

        if (isset($config['output_format']) && is_array($config['output_format'])) {
            $projectSettings->setOutputFormats($config['output_format']);
        }

        if (isset($config['show_progress'])) {
            $projectSettings->setShowProgressBar((bool) $config['show_progress']);
        }

        if (isset($config['fail_on_log'])) {
            $projectSettings->setFailOnError((bool) $config['show_progress']);
        }

        $container->getDefinition(SettingsManager::class)
            ->addMethodCall('setProjectSettings', [$projectSettings]);

        $config['base_template_paths'][] = dirname(__DIR__, 2) . '/resources/template/html';
        $container->setParameter('phpdoc.guides.base_template_paths', $config['base_template_paths']);

        foreach ($config['themes'] as $themeName => $themeConfig) {
            $container->getDefinition(ThemeManager::class)
                ->addMethodCall('registerTheme', [new ThemeConfig($themeName, $themeConfig['templates'], $themeConfig['extends'] ?? null)]);
        }
    }

    public function process(ContainerBuilder $container): void
    {
        (new NodeRendererPass())->process($container);
        (new ParserRulesPass())->process($container);
    }

    /** @param mixed[] $config */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }
}
